<?php

declare(strict_types=1);

class PostGallery
{
    public static function forPost(int $postId): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM post_gallery WHERE post_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public static function add(int $postId, string $image, string $caption = '', int $sort = 0): int
    {
        $stmt = db()->prepare(
            'INSERT INTO post_gallery (post_id, image, caption, sort_order) VALUES (?,?,?,?)'
        );
        $stmt->execute([$postId, $image, $caption, $sort]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('SELECT image FROM post_gallery WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_upload($row['image']);
        }
        db()->prepare('DELETE FROM post_gallery WHERE id = ?')->execute([$id]);
    }

    public static function deleteForPost(int $postId): void
    {
        foreach (self::forPost($postId) as $img) {
            delete_upload($img['image']);
        }
        db()->prepare('DELETE FROM post_gallery WHERE post_id = ?')->execute([$postId]);
    }
}
