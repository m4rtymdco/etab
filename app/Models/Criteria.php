<?php

class Criteria
{
    public static function forEvent(int $eventId): array
    {
        return Database::query(
            'SELECT * FROM criteria WHERE event_id = ? ORDER BY sort_order, id',
            [$eventId]
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        return Database::query('SELECT * FROM criteria WHERE id = ?', [$id])->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO criteria (event_id, name, description, section, max_score, weight, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['event_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['section'] ?? null,
                $data['max_score'] ?? 100,
                $data['weight'],
                $data['sort_order'] ?? 0,
            ]
        );
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE criteria SET name=?, description=?, section=?, max_score=?, weight=?, sort_order=? WHERE id=?',
            [
                $data['name'],
                $data['description'] ?? null,
                $data['section'] ?? null,
                $data['max_score'] ?? 100,
                $data['weight'],
                $data['sort_order'] ?? 0,
                $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM criteria WHERE id=?', [$id]);
    }

    public static function applyTemplate(int $eventId, int $templateId): void
    {
        $items = Database::query(
            'SELECT * FROM criteria_template_items WHERE template_id = ? ORDER BY sort_order, id',
            [$templateId]
        )->fetchAll();
        foreach ($items as $item) {
            self::create([
                'event_id' => $eventId,
                'name' => $item['name'],
                'description' => $item['description'],
                'section' => $item['section'] ?? null,
                'max_score' => $item['max_score'] ?? 100,
                'weight' => $item['weight'],
                'sort_order' => $item['sort_order'],
            ]);
        }
    }
}

class CriteriaTemplate
{
    public static function all(): array
    {
        return Database::query('SELECT * FROM criteria_templates ORDER BY name')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $t = Database::query('SELECT * FROM criteria_templates WHERE id=?', [$id])->fetch();
        if (!$t) {
            return null;
        }
        $t['items'] = Database::query(
            'SELECT * FROM criteria_template_items WHERE template_id=? ORDER BY sort_order, id',
            [$id]
        )->fetchAll();
        return $t;
    }

    public static function create(string $name, ?string $description, array $items): int
    {
        Database::query(
            'INSERT INTO criteria_templates (name, description, created_by) VALUES (?, ?, ?)',
            [$name, $description, Auth::id()]
        );
        $id = (int) Database::connection()->lastInsertId();
        foreach ($items as $item) {
            if (empty($item['name'])) {
                continue;
            }
            Database::query(
                'INSERT INTO criteria_template_items (template_id, name, description, section, max_score, weight, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $id,
                    $item['name'],
                    $item['description'] ?? null,
                    $item['section'] ?? null,
                    $item['max_score'] ?? 100,
                    $item['weight'] ?? 0,
                    $item['sort_order'] ?? 0,
                ]
            );
        }
        return $id;
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM criteria_templates WHERE id=?', [$id]);
    }

    public static function fromEvent(int $eventId, string $name): int
    {
        $criteria = Criteria::forEvent($eventId);
        $items = [];
        foreach ($criteria as $c) {
            $items[] = [
                'name' => $c['name'],
                'description' => $c['description'],
                'section' => $c['section'] ?? null,
                'max_score' => $c['max_score'] ?? 100,
                'weight' => $c['weight'],
                'sort_order' => $c['sort_order'] ?? 0,
            ];
        }
        return self::create($name, 'Saved from event #' . $eventId, $items);
    }
}
