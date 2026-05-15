<?php

declare(strict_types=1);

class CharityMember
{
    public static function active(): array
    {
        $stmt = db()->query(
            'SELECT * FROM charity_members WHERE is_active = 1 ORDER BY sort_order ASC, name ASC'
        );
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT * FROM charity_members ORDER BY sort_order ASC, name ASC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM charity_members WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $stmt = db()->prepare(
                'UPDATE charity_members SET name=?, position=?, photo=?, sort_order=?, is_active=? WHERE id=?'
            );
            $stmt->execute([
                $data['name'],
                $data['position'] ?? '',
                $data['photo'],
                (int) ($data['sort_order'] ?? 0),
                !empty($data['is_active']) ? 1 : 0,
                $id,
            ]);
            return $id;
        }

        $stmt = db()->prepare(
            'INSERT INTO charity_members (name, position, photo, sort_order, is_active) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['name'],
            $data['position'] ?? '',
            $data['photo'],
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $row = self::find($id);
        if ($row && $row['photo']) {
            delete_member_photo($row['photo']);
        }
        db()->prepare('DELETE FROM charity_members WHERE id = ?')->execute([$id]);
    }
}
