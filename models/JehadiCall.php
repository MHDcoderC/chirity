<?php

declare(strict_types=1);

class JehadiCall
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM jehadi_calls WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::withCounts($row) : null;
    }

    public static function active(): ?array
    {
        $stmt = db()->query(
            "SELECT * FROM jehadi_calls WHERE status = 'active' ORDER BY id DESC LIMIT 1"
        );
        $row = $stmt->fetch();
        return $row ? self::withCounts($row) : null;
    }

    public static function hasActive(?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM jehadi_calls WHERE status = 'active'";
        $params = [];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT * FROM jehadi_calls ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'withCounts'], $rows);
    }

    public static function registrationCount(int $callId): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM jehadi_registrations WHERE call_id = ?');
        $stmt->execute([$callId]);
        return (int) $stmt->fetchColumn();
    }

    public static function isOpenForRegistration(array $call): bool
    {
        if (($call['status'] ?? '') !== 'active') {
            return false;
        }
        $today = date('Y-m-d');
        if ($today < $call['date_from'] || $today > $call['date_to']) {
            return false;
        }
        $count = (int) ($call['registration_count'] ?? self::registrationCount((int) $call['id']));
        return $count < (int) $call['max_registrations'];
    }

    public static function isFull(array $call): bool
    {
        $count = (int) ($call['registration_count'] ?? self::registrationCount((int) $call['id']));
        return $count >= (int) $call['max_registrations'];
    }

    public static function save(array $data, ?int $id = null): int
    {
        $title = limit_text_field(trim($data['title'] ?? ''), JEHADI_TITLE_MAX);
        if ($title === '') {
            throw new InvalidArgumentException('عنوان فراخوان الزامی است.');
        }

        $status = ($data['status'] ?? 'active') === 'cancelled' ? 'cancelled' : 'active';

        if ($status === 'active' && self::hasActive($id)) {
            throw new RuntimeException('یک فراخوان فعال دیگر وجود دارد. ابتدا آن را لغو کنید.');
        }

        if ($id) {
            $stmt = db()->prepare(
                'UPDATE jehadi_calls SET title=?, description=?, banner=?, date_from=?, date_to=?,
                 max_registrations=?, status=? WHERE id=?'
            );
            $stmt->execute([
                $title,
                $data['description'],
                $data['banner'],
                $data['date_from'],
                $data['date_to'],
                max(1, (int) ($data['max_registrations'] ?? 50)),
                $status,
                $id,
            ]);
            return $id;
        }

        if ($status === 'active' && self::hasActive()) {
            throw new RuntimeException('تا زمان وجود فراخوان فعال، فراخوان جدید فعال قابل ایجاد نیست.');
        }

        $stmt = db()->prepare(
            'INSERT INTO jehadi_calls (title, description, banner, date_from, date_to, max_registrations, status)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $title,
            $data['description'],
            $data['banner'],
            $data['date_from'],
            $data['date_to'],
            max(1, (int) ($data['max_registrations'] ?? 50)),
            $status,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function cancel(int $id): void
    {
        db()->prepare("UPDATE jehadi_calls SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    }

    public static function delete(int $id): void
    {
        $row = self::find($id);
        if ($row && $row['banner']) {
            delete_jehadi_banner($row['banner']);
        }
        db()->prepare('DELETE FROM jehadi_calls WHERE id = ?')->execute([$id]);
    }

    private static function withCounts(array $row): array
    {
        $row['registration_count'] = self::registrationCount((int) $row['id']);
        $row['spots_left'] = max(0, (int) $row['max_registrations'] - $row['registration_count']);
        return $row;
    }
}
