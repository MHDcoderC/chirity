<?php

declare(strict_types=1);

class Category
{
    public static function all(): array
    {
        $stmt = db()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id AND p.status = \'publish\') AS post_count
             FROM categories c ORDER BY c.sort_order ASC, c.name ASC'
        );
        return $stmt->fetchAll();
    }

    public static function forHeader(): array
    {
        $stmt = db()->query(
            'SELECT * FROM categories WHERE show_in_header = 1 ORDER BY sort_order ASC, name ASC'
        );
        return $stmt->fetchAll();
    }

    public static function forHome(): array
    {
        $stmt = db()->query(
            'SELECT * FROM categories WHERE show_on_home = 1 ORDER BY sort_order ASC, name ASC'
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function forSelect(): array
    {
        return self::all();
    }

    public static function save(array $data, ?int $id = null): int
    {
        $slug = unique_slug($data['slug'] ?: $data['name'], 'categories', $id);
        $showHeader = !empty($data['show_in_header']) ? 1 : 0;
        $showHome = !empty($data['show_on_home']) ? 1 : 0;
        $homeLimit = max(1, min(12, (int) ($data['home_posts_limit'] ?? 4)));

        if ($id) {
            $stmt = db()->prepare(
                'UPDATE categories SET parent_id=?, name=?, slug=?, description=?, sort_order=?,
                 show_in_header=?, show_on_home=?, home_posts_limit=? WHERE id=?'
            );
            $stmt->execute([
                $data['parent_id'] ?: null,
                $data['name'],
                $slug,
                $data['description'] ?? '',
                (int) ($data['sort_order'] ?? 0),
                $showHeader,
                $showHome,
                $homeLimit,
                $id,
            ]);
            return $id;
        }

        $stmt = db()->prepare(
            'INSERT INTO categories (parent_id, name, slug, description, sort_order, show_in_header, show_on_home, home_posts_limit)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['parent_id'] ?: null,
            $data['name'],
            $slug,
            $data['description'] ?? '',
            (int) ($data['sort_order'] ?? 0),
            $showHeader,
            $showHome,
            $homeLimit,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM posts WHERE category_id = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
        db()->prepare('UPDATE categories SET parent_id = NULL WHERE parent_id = ?')->execute([$id]);
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        return true;
    }
}
