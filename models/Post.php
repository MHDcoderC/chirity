<?php

declare(strict_types=1);

class Post
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    u.display_name AS author_name
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN users u ON u.id = p.author_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                       u.display_name AS author_name
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN users u ON u.id = p.author_id
                WHERE p.slug = ?';
        if ($publishedOnly) {
            $sql .= " AND p.status = 'publish'";
        }
        $stmt = db()->prepare($sql);
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function adminList(?string $status = null, ?int $categoryId = null, int $page = 1): array
    {
        $where = ['1=1'];
        $params = [];

        if ($status) {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }
        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * POSTS_PER_PAGE);

        $countStmt = db()->prepare("SELECT COUNT(*) FROM posts p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT p.*, c.name AS category_name, u.display_name AS author_name
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN users u ON u.id = p.author_id
                WHERE {$whereSql}
                ORDER BY p.updated_at DESC
                LIMIT " . POSTS_PER_PAGE . " OFFSET {$offset}";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int) ceil($total / POSTS_PER_PAGE),
            'page'  => $page,
        ];
    }

    public static function publishedList(?int $categoryId = null, int $page = 1, int $limit = POSTS_PER_PAGE): array
    {
        $where = ["p.status = 'publish'"];
        $params = [];

        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $limit);

        $countStmt = db()->prepare("SELECT COUNT(*) FROM posts p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE {$whereSql}
                ORDER BY p.published_at DESC, p.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
            'page'  => $page,
        ];
    }

    public static function featured(int $limit = 5): array
    {
        $stmt = db()->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'publish'
             ORDER BY p.published_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function ticker(int $limit = 10): array
    {
        $stmt = db()->prepare(
            "SELECT p.id, p.title, p.slug, c.name AS category_name
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'publish' AND p.show_in_ticker = 1
             ORDER BY p.published_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function publishedInCategory(int $categoryId, int $limit = 4): array
    {
        $result = self::publishedList($categoryId, 1, $limit);
        return $result['items'];
    }

    public static function byCategorySlug(string $slug, int $limit = 6): array
    {
        $cat = Category::findBySlug($slug);
        if (!$cat) {
            return [];
        }
        $result = self::publishedList((int) $cat['id'], 1, $limit);
        return $result['items'];
    }

    public static function incrementViews(int $id): void
    {
        db()->prepare('UPDATE posts SET view_count = view_count + 1 WHERE id = ?')->execute([$id]);
    }

    public static function save(array $data, int $authorId, ?int $id = null): int
    {
        $data = normalize_post_fields($data);
        $slug = unique_slug($data['slug'] ?: $data['title'], 'posts', $id);
        $publishedAt = null;

        if ($data['status'] === 'publish') {
            $publishedAt = $data['published_at'] ?? date('Y-m-d H:i:s');
        }

        $ticker = !empty($data['show_in_ticker']) ? 1 : 0;
        $comments = !isset($data['allow_comments']) || $data['allow_comments'] ? 1 : 0;

        if ($id) {
            $stmt = db()->prepare(
                'UPDATE posts SET category_id=?, title=?, slug=?, excerpt=?, content=?,
                 featured_image=?, status=?, published_at=?, show_in_ticker=?, allow_comments=? WHERE id=?'
            );
            $stmt->execute([
                $data['category_id'] ?: null,
                $data['title'],
                $slug,
                $data['excerpt'] ?? '',
                $data['content'],
                $data['featured_image'] ?? null,
                $data['status'],
                $publishedAt,
                $ticker,
                $comments,
                $id,
            ]);
            return $id;
        }

        $stmt = db()->prepare(
            'INSERT INTO posts (author_id, category_id, title, slug, excerpt, content,
             featured_image, status, published_at, show_in_ticker, allow_comments) VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $authorId,
            $data['category_id'] ?: null,
            $data['title'],
            $slug,
            $data['excerpt'] ?? '',
            $data['content'],
            $data['featured_image'] ?? null,
            $data['status'],
            $publishedAt,
            $ticker,
            $comments,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $post = self::find($id);
        if ($post) {
            delete_upload($post['featured_image']);
        }
        PostGallery::deleteForPost($id);
        db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
    }

    public static function counts(): array
    {
        $stmt = db()->query(
            "SELECT status, COUNT(*) AS cnt FROM posts GROUP BY status"
        );
        $out = ['draft' => 0, 'publish' => 0, 'pending' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['status']] = (int) $row['cnt'];
        }
        return $out;
    }
}
