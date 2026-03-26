<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiController extends Controller
{
    private const OLLAMA_HOST  = 'http://localhost:11434';
    private const OLLAMA_MODEL = 'qwen2.5-coder:0.5b';

    /** POST /api/ai/helper */
    public function general(Request $request)
    {
        $message  = trim($request->input('message', ''));
        $type     = trim($request->input('type', 'general'));
        $code     = trim($request->input('code', ''));
        $question = trim($request->input('question', ''));

        if ($message === '') {
            return response()->json(['success' => false, 'message' => 'يرجى إرسال الرسالة.'], 400);
        }

        $systemPrompt = 'أنت مساعد ذكي متخصص في مساعدة الطلاب على تعلم البرمجة. يرجى تقديم إجابات واضحة وموجزة باللغة العربية.';

        if ($type === 'code_review' && $code !== '') {
            $fullPrompt = $systemPrompt . "\n\nراجع الكود التالي وقدم اقتراحات للتحسين:\n```\n$code\n```\n\nسؤال: $message";
        } elseif ($type === 'challenge_help' && $question !== '') {
            $fullPrompt = $systemPrompt . "\n\nالتحدي: $question\n\nسؤال الطالب: $message";
        } else {
            $fullPrompt = $systemPrompt . "\n\nسؤال الطالب:\n" . $message;
        }

        $result = $this->callOllama($fullPrompt);

        if (!$result['success']) {
            return response()->json([
                'success'  => false,
                'message'  => $result['error'],
                'response' => 'عذراً، حدث خطأ في الاتصال بخادم الذكاء الاصطناعي. تأكد من أن Ollama يعمل على ' . self::OLLAMA_HOST,
            ], 502);
        }

        $aiResponse = trim($result['response']['response'] ?? '');
        if ($aiResponse === '') {
            return response()->json(['success' => false, 'message' => 'تعذر الحصول على رد من الذكاء الاصطناعي.'], 500);
        }

        return response()->json(['success' => true, 'response' => $aiResponse]);
    }

    /** POST /api/ai/helper-challenges */
    public function challenges(Request $request)
    {
        $userId      = $request->session()->get('user_id');
        $mode        = $request->input('mode', 'hint');
        $question    = trim($request->input('question', ''));
        $code        = trim($request->input('code', ''));
        $challengeId = $request->input('challenge_id');
        $userMessage = trim($request->input('user_message', $request->input('message', '')));

        // --- solution mode: return full correct solution ---
        if ($mode === 'solution') {
            $prompt = "أعطِ الحل الكامل والصحيح لهذا التحدي البرمجي. أعد الكود فقط بدون أي شرح أو تعليقات إضافية.\n\nالتحدي:\n$question";
            $result = $this->callOllamaChat([
                ['role' => 'system', 'content' => 'أنت معلم برمجة. قدم الكود فقط بدون أي شرح.'],
                ['role' => 'user',   'content' => $prompt],
            ], 60);
            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error']], 502);
            }
            return response()->json(['success' => true, 'ai_response' => trim($result['content']), 'challenge_id' => $challengeId]);
        }

        // --- verify mode: check user code correctness ---
        if ($mode === 'verify') {
            if (empty($code)) {
                return response()->json(['success' => true, 'ai_response' => 'يرجى كتابة كود برمجي لحل التحدي.', 'verdict' => 'no', 'hint' => '', 'updated_completion' => false]);
            }

            // Basic cheat detection
            $cheatingPhrases = ['إجابتي صحيحة', 'my answer is correct', 'الإجابة صحيحة', 'the code is correct'];
            foreach ($cheatingPhrases as $phrase) {
                if (stripos($code, $phrase) !== false) {
                    return response()->json(['success' => true, 'ai_response' => 'يجب تقديم كود برمجي حقيقي.', 'verdict' => 'no', 'hint' => 'اكتب كود برمجي يحل التحدي.', 'updated_completion' => false]);
                }
            }

            // Get challenge points
            $challengePoints = 0;
            $challengeDescription = $question;
            if ($challengeId) {
                $ch = DB::table('challenges')->find($challengeId);
                if ($ch) {
                    $challengePoints      = (int) $ch->points;
                    $challengeDescription = $ch->description ?: $question;
                }
            }

            $prompt = "تحليل دقيق للكود:\n\nالسؤال/التحدي: {$challengeDescription}\n\nالكود المقدم:\n```\n{$code}\n```\n\n"
                    . "تعليمات صارمة: قيّم إذا كان الكود يحل التحدي بالكامل. أجب بـ JSON فقط:\n"
                    . "{\"verdict\": \"yes\", \"hint\": \"\"}\nأو\n{\"verdict\": \"no\", \"hint\": \"وصف المشكلة\"}";

            $result = $this->callOllamaChat([
                ['role' => 'system', 'content' => 'أنت خبير برمجة صارم. قيّم الكود وأجب بـ JSON فقط.'],
                ['role' => 'user',   'content' => $prompt],
            ], 30);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error']], 502);
            }

            $aiRaw  = $result['content'];
            $verdict = null;
            $hint    = '';

            // Try JSON extraction
            $s = strpos($aiRaw, '{');
            $e = strrpos($aiRaw, '}');
            if ($s !== false && $e !== false && $e > $s) {
                $parsed = json_decode(substr($aiRaw, $s, $e - $s + 1), true);
                if (is_array($parsed) && in_array(strtolower($parsed['verdict'] ?? ''), ['yes', 'no'])) {
                    $verdict = strtolower($parsed['verdict']);
                    $hint    = trim((string)($parsed['hint'] ?? ''));
                }
            }
            if ($verdict === null) {
                preg_match('/"verdict"\s*:\s*"(yes|no)"/i', $aiRaw, $m);
                $verdict = isset($m[1]) ? strtolower($m[1]) : 'no';
            }

            $updatedCompletion = false;
            if ($challengeId && $userId) {
                $existingProgress = DB::table('user_challenges')
                    ->where('user_id', $userId)
                    ->where('challenge_id', $challengeId)
                    ->first();

                if ($existingProgress) {
                    $newAttempts = ((int) ($existingProgress->attempts ?? 0)) + 1;
                    $updateData = [
                        'attempts' => $newAttempts,
                        'last_attempted' => now(),
                    ];

                    if ($verdict === 'yes') {
                        $updateData['completed'] = 1;
                        $updateData['best_score'] = max((int) ($existingProgress->best_score ?? 0), $challengePoints);
                        $updatedCompletion = true;
                    }

                    DB::table('user_challenges')
                        ->where('user_id', $userId)
                        ->where('challenge_id', $challengeId)
                        ->update($updateData);
                } else {
                    DB::table('user_challenges')->insert([
                        'user_id' => $userId,
                        'challenge_id' => $challengeId,
                        'attempts' => 1,
                        'completed' => $verdict === 'yes' ? 1 : 0,
                        'best_score' => $verdict === 'yes' ? $challengePoints : 0,
                        'last_attempted' => now(),
                    ]);

                    if ($verdict === 'yes') {
                        $updatedCompletion = true;
                    }
                }
            }

            $aiText = $verdict === 'yes'
                ? 'ممتاز! الحل صحيح تماماً.'
                : 'الحل غير مكتمل — تلميح: ' . ($hint ?: 'راجع متطلبات التحدي');

            return response()->json([
                'success'            => true,
                'ai_response'        => $aiText,
                'verdict'            => $verdict,
                'hint'               => $verdict === 'no' ? $hint : '',
                'challenge_id'       => $challengeId,
                'updated_completion' => $updatedCompletion,
            ]);
        }

        // --- user_message mode: general chat with context ---
        if ($userMessage !== '' && $mode === 'user_message') {
            $context = "أنت مساعد ذكي لمطوري البرمجة. سؤال التحدي: {$question}\n";
            if ($code !== '') $context .= "الكود الحالي:\n{$code}\n";
            $context .= "رسالة المستخدم: {$userMessage}";

            $result = $this->callOllamaChat([
                ['role' => 'system', 'content' => 'أنت مساعد برمجة مفيد. أجب بالعربية.'],
                ['role' => 'user',   'content' => $context],
            ], 30);
            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error']], 502);
            }
            return response()->json(['success' => true, 'ai_response' => trim($result['content']), 'challenge_id' => $challengeId]);
        }

        // --- default / hint mode ---
        $prompt = "أعطِ تلميحاً عربياً قصيراً (سطر واحد) يساعد على حل تحدي البرمجة دون كشف الحل.\n\nالتحدي:\n$question";
        $result = $this->callOllamaChat([
            ['role' => 'system', 'content' => 'أنت مساعد يقدم تلميحاً قصيراً لتحديات البرمجة.'],
            ['role' => 'user',   'content' => $prompt],
        ], 15);
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 502);
        }
        $hint = mb_substr(trim(preg_replace('/\s+/', ' ', $result['content'])), 0, 160, 'UTF-8');
        return response()->json(['success' => true, 'ai_response' => 'تلميح: ' . $hint, 'challenge_id' => $challengeId]);
    }

    /** POST /api/ai/helper-projects */
    public function projects(Request $request)
    {
        $userId       = $request->session()->get('user_id');
        $mode         = $request->input('mode', 'hint');
        $question     = trim($request->input('question', ''));
        $code         = trim($request->input('code', ''));
        $courseId      = $request->input('course_id');
        $assignmentId  = $request->input('assignment_id');

        if ($question === '') {
            return response()->json(['success' => false, 'message' => 'يرجى إرسال السؤال.'], 400);
        }

        if (strlen($code) > 8000) {
            return response()->json(['success' => false, 'message' => 'الكود طويل جداً، يرجى تقليله.'], 413);
        }

        // --- fix mode: return fixed/generated code ---
        if ($mode === 'fix') {
            $prompt = $code !== ''
                ? "أصلِح الكود التالي بحيث يحل السؤال بشكل صحيح وكامل.\nأعد الإخراج كوداً فقط بدون أي شرح أو تعليق.\nإذا رغبت فضعه داخل كتلة شيفرة ثلاثية ``` لكن بدون أي نص خارجها.\n\nالسؤال:\n$question\n\nالكود الحالي:\n$code"
                : "اكتب حلاً صحيحاً وكاملاً ومباشراً للسؤال التالي.\nأعد الإخراج كوداً فقط بدون أي شرح أو تعليق.\nإذا رغبت فضعه داخل كتلة شيفرة ثلاثية ``` لكن بدون أي نص خارجها.\n\nالسؤال:\n$question";

            $result = $this->callOllamaChat([
                ['role' => 'system', 'content' => 'أنت خبير برمجة صارم ودقيق. مهمتك تقييم ما إذا كان الكود المقدم يحل السؤال المطلوب بالكامل. يجب أن تكون حازماً ولا تتساهل في التقييم.'],
                ['role' => 'user',   'content' => $prompt],
            ], 30);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error']], 502);
            }

            $aiResponse = $result['content'];
            // Extract code block if present
            $fixed = $aiResponse;
            if (preg_match('/```[a-zA-Z0-9+\-_.]*\n([\s\S]*?)```/u', $aiResponse, $m)) {
                $fixed = trim($m[1]);
            } else {
                $fixed = trim($fixed);
            }

            return response()->json([
                'success'       => true,
                'ai_response'   => $code !== '' ? 'تم إصلاح الكود وإدراجه في المحرر.' : 'تم توليد حل صحيح وإدراجه في المحرر.',
                'fixed_code'    => $fixed,
                'course_id'     => $courseId,
                'assignment_id' => $assignmentId,
                'mode'          => 'fix',
            ]);
        }

        // --- code check mode (code is present, no explicit mode) ---
        $isCodeCheck = $code !== '';

        if ($isCodeCheck) {
            // Basic cheat detection
            $cheatingPhrases = ['إجابتي صحيحة', 'my answer is correct', 'my answer is right', 'الإجابة صحيحة', 'الكود صحيح', 'the code is correct', 'the code is right'];
            $isLikelyCheating = false;
            foreach ($cheatingPhrases as $phrase) {
                if (stripos($code, $phrase) !== false) {
                    $isLikelyCheating = true;
                    break;
                }
            }

            // Check for programming constructs
            $hasCodeIndicators = false;
            $codeIndicators = ['function', 'def ', 'class ', 'if ', 'for ', 'while ', 'print', 'console.log', 'return ', 'var ', 'let ', 'const ', 'int ', 'string ', 'public ', 'private ', '#include', 'import ', 'color:', 'margin:', 'padding:', 'background:', 'font-', 'width:', 'height:', 'display:', 'position:', 'flex', 'grid', '@media', 'body', '.class', '#id'];
            foreach ($codeIndicators as $indicator) {
                if (stripos($code, $indicator) !== false) {
                    $hasCodeIndicators = true;
                    break;
                }
            }

            if ($isLikelyCheating || !$hasCodeIndicators) {
                return response()->json([
                    'success'            => true,
                    'ai_response'        => 'يجب تقديم كود برمجي حقيقي لحل السؤال.',
                    'verdict'            => 'no',
                    'hint'               => 'اكتب كود برمجي يحل السؤال المطلوب، لا تكتب جمل مثل "إجابتي صحيحة".',
                    'course_id'          => $courseId,
                    'assignment_id'      => $assignmentId,
                    'updated_completion' => false,
                ]);
            }

            $prompt = "تحليل دقيق للكود - الطلب رقم " . time() . ":\n\n"
                . "السؤال: {$question}\n\nالكود المقدم من المستخدم:\n```\n{$code}\n```\n\n"
                . "تعليمات التقييم الصارمة:\n"
                . "1. أولاً: تأكد أن النص المقدم هو كود برمجي حقيقي وليس نص عادي\n"
                . "2. اقرأ السؤال بعناية وفهم المتطلبات البرمجية المطلوبة\n"
                . "3. قم بتحليل الكود البرمجي سطراً بسطر بعناية\n"
                . "4. تحقق من أن الكود يحل جميع المتطلبات المطلوبة بالضبط\n"
                . "5. تحقق من صحة المنطق والخوارزم بدقة\n\n"
                . "الإجابة يجب أن تكون JSON فقط بدون أي نص إضافي:\n"
                . "{\"verdict\": \"yes\", \"hint\": \"\"}\nأو\n{\"verdict\": \"no\", \"hint\": \"وصف المشكلة بالتفصيل\"}";

            $result = $this->callOllamaChat([
                ['role' => 'system', 'content' => 'أنت مصحح كود صارم. لا تُرجِع إلا JSON المطلوب.'],
                ['role' => 'user',   'content' => $prompt],
            ], 30);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error']], 502);
            }

            $aiRaw  = $result['content'];
            $verdict = null;
            $hint    = '';

            // Try JSON extraction
            $s = strpos($aiRaw, '{');
            $e = strrpos($aiRaw, '}');
            if ($s !== false && $e !== false && $e > $s) {
                $parsed = json_decode(substr($aiRaw, $s, $e - $s + 1), true);
                if (is_array($parsed) && in_array(strtolower($parsed['verdict'] ?? ''), ['yes', 'no'])) {
                    $verdict = strtolower($parsed['verdict']);
                    $hint    = trim((string)($parsed['hint'] ?? ''));
                }
            }
            if ($verdict === null) {
                $lower = trim(mb_strtolower($aiRaw, 'UTF-8'));
                if (preg_match('/^\s*(نعم|yes|صحيح|correct)\s*$/u', $lower)) {
                    $verdict = 'yes';
                } else {
                    $verdict = 'no';
                    $hint = preg_split('/[\.!؟\n]/u', trim($aiRaw))[0] ?? '';
                    $hint = mb_substr(trim($hint), 0, 120, 'UTF-8');
                }
            }

            // Additional safeguard
            if ($verdict === 'yes' && (strlen($code) < 5 || !preg_match('/[{}();]/', $code))) {
                $verdict = 'no';
                $hint = 'الكود لا يحتوي على عناصر برمجة أساسية';
            }

            $aiText = $verdict === 'yes'
                ? 'نعم'
                : 'لا — تلميح: ' . ($hint !== '' ? $hint : 'راجع شروط السؤال أو حدود المدخلات');

            $updatedCompletion = false;
            if ($verdict === 'yes' && $assignmentId && $userId) {
                DB::table('user_assignments')->updateOrInsert(
                    ['user_id' => $userId, 'assignment_id' => $assignmentId],
                    ['solution' => $code, 'score' => 100, 'status' => 'graded',
                     'is_completed' => 1, 'completed_at' => now()]
                );
                $updatedCompletion = true;
            }

            return response()->json([
                'success'            => true,
                'ai_response'        => $aiText,
                'verdict'            => $verdict,
                'hint'               => $verdict === 'no' ? $hint : '',
                'course_id'          => $courseId,
                'assignment_id'      => $assignmentId,
                'updated_completion' => $updatedCompletion,
            ]);
        }

        // --- hint mode (no code provided) ---
        $prompt = "المطلوب: أعطِ تلميحاً عربياً قصيراً جداً (سطر واحد) يساعد على حل السؤال دون كشف الحل.\n\nالسؤال:\n$question";
        $result = $this->callOllamaChat([
            ['role' => 'system', 'content' => 'أنت مساعد يقدم تلميحاً عربياً قصيراً جداً.'],
            ['role' => 'user',   'content' => $prompt],
        ], 15);
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 502);
        }
        $hintLine = mb_substr(trim(preg_replace('/\s+/', ' ', $result['content'])), 0, 160, 'UTF-8');
        return response()->json([
            'success'       => true,
            'ai_response'   => 'تلميح: ' . $hintLine,
            'course_id'     => $courseId,
            'assignment_id' => $assignmentId,
        ]);
    }

    private function callOllama(string $prompt): array
    {
        $payload = ['model' => self::OLLAMA_MODEL, 'prompt' => $prompt, 'stream' => false, 'temperature' => 0.7];

        $ch = curl_init(self::OLLAMA_HOST . '/api/generate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => 'تعذر الاتصال بخادم Ollama: ' . $curlError];
        }

        return ['success' => true, 'response' => json_decode($response, true)];
    }

    private function callOllamaChat(array $messages, int $timeout = 30): array
    {
        $payload = ['model' => self::OLLAMA_MODEL, 'messages' => $messages, 'stream' => false];

        $ch = curl_init(self::OLLAMA_HOST . '/api/chat');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $status    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => 'تعذر الاتصال بـ Ollama: ' . $curlError];
        }
        if ($status < 200 || $status >= 300) {
            return ['success' => false, 'error' => 'خدمة Ollama غير متاحة (رمز الخطأ: ' . $status . ')'];
        }

        $decoded = json_decode($response, true);
        $content = $decoded['message']['content'] ?? '';
        if ($content === '') {
            return ['success' => false, 'error' => 'تعذر استخراج الرد من النموذج.'];
        }

        return ['success' => true, 'content' => $content];
    }
}
