<?php

class Audit
{
    public static function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, $details = null): void
    {
        try {
            Database::query(
                'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    $action,
                    $entityType,
                    $entityId,
                    is_string($details) || $details === null ? $details : json_encode($details),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );
        } catch (Throwable $e) {
            // Never block the request on audit failure
        }
    }
}
