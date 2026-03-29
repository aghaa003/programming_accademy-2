<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * M3: Lightweight audit logger for admin mutations.
 * Writes to admin_audit_logs; falls back to the application log on DB failure.
 */
class AuditLogger
{
    /**
     * @param  Request          $request   Current HTTP request (for admin_id and IP)
     * @param  string           $action    Snake-case verb, e.g. 'create_challenge'
     * @param  string|null      $targetType  Model class short-name, e.g. 'Challenge'
     * @param  int|null         $targetId
     * @param  array|null       $payload   Arbitrary context (never include passwords)
     */
    public static function log(
        Request $request,
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $payload = null
    ): void {
        try {
            DB::table('admin_audit_logs')->insert([
                'admin_id'    => auth()->id(),
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'payload'     => $payload !== null ? json_encode($payload) : null,
                'ip'          => $request->ip(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit failures must never bring down the admin operation
            Log::error('AuditLogger::log failed', [
                'action'     => $action,
                'target'     => "$targetType#$targetId",
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
