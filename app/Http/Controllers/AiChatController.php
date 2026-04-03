<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    /** GET /api/ai/conversations */
    public function index()
    {
        $userId = auth()->id();

        $conversations = AiConversation::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'created_at', 'updated_at'])
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'title' => $conv->title ?: 'محادثة جديدة',
                    'created_at' => $conv->created_at,
                    'updated_at' => $conv->updated_at,
                ];
            });

        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    /** POST /api/ai/conversations */
    public function store()
    {
        $conv = AiConversation::create([
            'user_id' => auth()->id(),
            'title' => null,
        ]);

        return response()->json(['success' => true, 'conversation' => [
            'id' => $conv->id,
            'title' => 'محادثة جديدة',
        ]], 201);
    }

    /** GET /api/ai/conversations/{id} */
    public function show(int $id)
    {
        $conv = AiConversation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $messages = $conv->messages()->get(['id', 'role', 'content', 'has_images', 'created_at']);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conv->id,
                'title' => $conv->title ?: 'محادثة جديدة',
            ],
            'messages' => $messages,
        ]);
    }

    /** DELETE /api/ai/conversations/{id} */
    public function destroy(int $id)
    {
        $conv = AiConversation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $conv->delete();

        return response()->json(['success' => true]);
    }

    /** PATCH /api/ai/conversations/{id}/title */
    public function rename(Request $request, int $id)
    {
        $conv = AiConversation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return response()->json(['success' => false, 'message' => 'العنوان لا يمكن أن يكون فارغاً.'], 422);
        }

        $conv->title = mb_substr($title, 0, 255);
        $conv->save();

        return response()->json(['success' => true, 'title' => $conv->title]);
    }

    /** POST /api/ai/conversations/{id}/messages */
    public function sendMessage(Request $request, int $id)
    {
        $conv = AiConversation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $message = trim((string) $request->input('message', ''));
        $code = trim((string) $request->input('code', ''));

        if ($message === '') {
            return response()->json(['success' => false, 'message' => 'يرجى إرسال الرسالة.'], 400);
        }
        if (strlen($message) > 2000) {
            return response()->json(['success' => false, 'message' => 'الرسالة طويلة جداً، يرجى تقليلها.'], 413);
        }
        if (strlen($code) > 100000) {
            return response()->json(['success' => false, 'message' => 'الكود طويل جداً، يرجى تقليله.'], 413);
        }

        // Accept images as proper file uploads — avoids base64 text-field size limits
        $uploadedImages = $request->file('images') ?? [];
        if (! is_array($uploadedImages)) {
            $uploadedImages = [$uploadedImages];
        }
        $allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $validImages = [];
        foreach (array_slice($uploadedImages, 0, 5) as $file) {
            if ($file instanceof UploadedFile
                && $file->isValid()
                && in_array(strtolower($file->getClientOriginalExtension()), $allowedImageExts)
                && $file->getSize() <= 10 * 1024 * 1024) {
                $raw = file_get_contents($file->getRealPath());
                if ($raw === false) {
                    continue;
                }
                $validImages[] = base64_encode($raw);
            }
        }
        $hasImages = count($validImages) > 0;

        // Build user prompt
        if ($code !== '') {
            $userPrompt = $message."\n\n```\n".$code."\n```";
        } else {
            $userPrompt = $message;
        }

        // Save user message
        AiMessage::create([
            'conversation_id' => $conv->id,
            'role' => 'user',
            'content' => $userPrompt,
            'has_images' => $hasImages,
        ]);

        // Auto-title on first message
        if ($conv->title === null) {
            $conv->title = mb_substr($message, 0, 60);
            $conv->save();
        }

        // Load last 30 messages as context
        $history = AiMessage::where('conversation_id', $conv->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        $ollamaMessages = [
            ['role' => 'system', 'content' => 'أنت مساعد ذكي متخصص في مساعدة الطلاب على تعلم البرمجة. يرجى تقديم إجابات واضحة وموجزة باللغة العربية.'],
        ];

        foreach ($history as $msg) {
            $ollamaMessages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        $visionModel = config('ai.ollama_vision_model', 'qwen3-vl:235b-cloud');

        // Use vision model if: current message has images OR any previous message
        // in this conversation had images (vision model handles text fine too).
        $hadImagesInHistory = $history->contains('has_images', true);
        $useVision = $hasImages || $hadImagesInHistory;

        $result = $this->callOllamaChat(
            $ollamaMessages,
            $useVision ? 90 : 60,
            0.5,
            $useVision ? $visionModel : null,
            $validImages
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'خدمة الذكاء الاصطناعي غير متاحة حالياً، يرجى المحاولة لاحقاً.',
            ], 502);
        }

        $aiResponse = trim($result['content'] ?? '');
        if ($aiResponse === '') {
            return response()->json(['success' => false, 'message' => 'تعذر الحصول على رد من الذكاء الاصطناعي.'], 500);
        }

        // Save assistant message and update conversation timestamp atomically
        $assistantMsg = DB::transaction(function () use ($conv, $aiResponse) {
            $msg = AiMessage::create([
                'conversation_id' => $conv->id,
                'role' => 'assistant',
                'content' => $aiResponse,
                'has_images' => false,
            ]);
            $conv->touch();

            return $msg;
        });

        return response()->json([
            'success' => true,
            'response' => $aiResponse,
            'title' => $conv->title,
            'message_id' => $assistantMsg->id,
        ]);
    }

    protected function callOllamaChat(array $messages, int $timeout = 30, float $temperature = 0.3, ?string $model = null, array $images = []): array
    {
        $modelToUse = $model ?? config('ai.ollama_model');

        // Attach images to the last user message
        if (! empty($images)) {
            for ($i = count($messages) - 1; $i >= 0; $i--) {
                if ($messages[$i]['role'] === 'user') {
                    $messages[$i]['images'] = $images;
                    break;
                }
            }
        }

        $payload = [
            'model' => $modelToUse,
            'messages' => $messages,
            'stream' => false,
            'keep_alive' => -1,
            'options' => ['temperature' => $temperature],
        ];

        $ch = curl_init(config('ai.ollama_host').'/api/chat');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => 'تعذر الاتصال بـ Ollama: '.$curlError];
        }
        if ($status < 200 || $status >= 300) {
            return ['success' => false, 'error' => 'خدمة Ollama غير متاحة (رمز الخطأ: '.$status.')'];
        }

        $decoded = json_decode($response, true);
        $content = $decoded['message']['content'] ?? '';
        if ($content === '') {
            return ['success' => false, 'error' => 'تعذر استخراج الرد من النموذج.'];
        }

        return ['success' => true, 'content' => $content];
    }
}
