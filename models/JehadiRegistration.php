<?php

declare(strict_types=1);

class JehadiRegistration
{
    public static function create(int $callId, array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO jehadi_registrations (call_id, full_name, personal_details, birth_date, national_id, mobile, ip_address)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $callId,
            $data['full_name'],
            $data['personal_details'] ?: null,
            $data['birth_date'],
            $data['national_id'],
            $data['mobile'],
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function existsNationalId(int $callId, string $nationalId): bool
    {
        $stmt = db()->prepare(
            'SELECT id FROM jehadi_registrations WHERE call_id = ? AND national_id = ? LIMIT 1'
        );
        $stmt->execute([$callId, $nationalId]);
        return (bool) $stmt->fetch();
    }

    public static function adminList(?int $callId = null): array
    {
        $sql = 'SELECT r.*, c.title AS call_title
                FROM jehadi_registrations r
                INNER JOIN jehadi_calls c ON c.id = r.call_id';
        $params = [];
        if ($callId) {
            $sql .= ' WHERE r.call_id = ?';
            $params[] = $callId;
        }
        $sql .= ' ORDER BY r.created_at DESC LIMIT 500';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
