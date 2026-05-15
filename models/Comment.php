<?php

declare(strict_types=1);

class Comment
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function forPost(int $postId, bool $approvedOnly = true): array
    {
        $sql = 'SELECT * FROM comments WHERE post_id = ? AND parent_id IS NULL';
        if ($approvedOnly) {
            $sql .= " AND status = 'approved'";
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute([$postId]);
        $roots = $stmt->fetchAll();

        foreach ($roots as &$root) {
            $root['replies'] = self::replies((int) $root['id'], $approvedOnly);
        }
        return $roots;
    }

    public static function replies(int $parentId, bool $approvedOnly): array
    {
        $sql = 'SELECT * FROM comments WHERE parent_id = ?';
        if ($approvedOnly) {
            $sql .= " AND status = 'approved'";
        }
        $sql .= ' ORDER BY created_at ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }

    public static function create(int $postId, string $name, string $email, string $body, ?int $parentId = null): int
    {
        $stmt = db()->prepare(
            'INSERT INTO comments (post_id, parent_id, author_name, author_email, body, status, ip_address)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $postId,
            $parentId,
            $name,
            $email !== '' ? $email : null,
            $body,
            'pending',
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function adminList(?string $status = null): array
    {
        $sql = 'SELECT c.*, p.title AS post_title, p.slug AS post_slug
                FROM comments c
                INNER JOIN posts p ON p.id = c.post_id';
        $params = [];
        if ($status) {
            $sql .= ' WHERE c.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY c.created_at DESC LIMIT 100';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function setStatus(int $id, string $status): void
    {
        $allowed = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            return;
        }
        db()->prepare('UPDATE comments SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$id]);
    }

    public static function countPending(): int
    {
        return (int) db()->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
    }
}
