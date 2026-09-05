<?php

class Contestant
{
    public static function find(int $id): ?array
    {
        return Database::query('SELECT * FROM contestants WHERE id = ?', [$id])->fetch() ?: null;
    }

    public static function all(bool $includeArchived = false): array
    {
        $sql = 'SELECT * FROM contestants';
        if (!$includeArchived) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= ' ORDER BY name';
        return Database::query($sql)->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::query("SELECT COUNT(*) c FROM contestants WHERE status='active'")->fetch()['c'];
    }

    public static function forEvent(int $eventId, bool $includeArchived = false): array
    {
        $sql = 'SELECT c.*, ce.entry_number FROM contestants c
                INNER JOIN contestant_events ce ON ce.contestant_id = c.id
                WHERE ce.event_id = ?';
        $params = [$eventId];
        if (!$includeArchived) {
            $sql .= " AND c.status = 'active'";
        }
        $sql .= ' ORDER BY ce.entry_number IS NULL, ce.entry_number, c.name';
        return Database::query($sql, $params)->fetchAll();
    }

    public static function normalizeDivision(?string $value): string
    {
        $v = strtolower(trim((string) $value));
        if (in_array($v, ['exclusive', 'excl'], true)) {
            return 'Exclusive';
        }
        return 'Open';
    }

    public static function divisionKey(?string $category): string
    {
        return self::normalizeDivision($category) === 'Exclusive' ? 'exclusive' : 'open';
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO contestants (name, category, photo_url, status, notes) VALUES (?, ?, ?, ?, ?)',
            [
                $data['name'],
                self::normalizeDivision($data['category'] ?? 'open'),
                $data['photo_url'] ?? null,
                $data['status'] ?? 'active',
                $data['notes'] ?? null,
            ]
        );
        $id = (int) Database::connection()->lastInsertId();
        if (!empty($data['event_id'])) {
            self::assignToEvent($id, (int) $data['event_id'], $data['entry_number'] ?? null);
        }
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE contestants SET name=?, category=?, photo_url=?, status=?, notes=? WHERE id=?',
            [
                $data['name'],
                self::normalizeDivision($data['category'] ?? 'open'),
                $data['photo_url'] ?? null,
                $data['status'] ?? 'active',
                $data['notes'] ?? null,
                $id,
            ]
        );
    }

    public static function archive(int $id): void
    {
        Database::query("UPDATE contestants SET status='archived' WHERE id=?", [$id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM contestants WHERE id=?', [$id]);
    }

    public static function assignToEvent(int $contestantId, int $eventId, ?string $entryNumber = null): void
    {
        Database::query(
            'INSERT INTO contestant_events (contestant_id, event_id, entry_number) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE entry_number = VALUES(entry_number)',
            [$contestantId, $eventId, $entryNumber]
        );
    }

    public static function unassignFromEvent(int $contestantId, int $eventId): void
    {
        Database::query(
            'DELETE FROM contestant_events WHERE contestant_id = ? AND event_id = ?',
            [$contestantId, $eventId]
        );
    }

    public static function events(int $contestantId): array
    {
        return Database::query(
            'SELECT e.*, ce.entry_number FROM events e
             INNER JOIN contestant_events ce ON ce.event_id = e.id
             WHERE ce.contestant_id = ? ORDER BY e.event_date DESC',
            [$contestantId]
        )->fetchAll();
    }

    public static function categories(): array
    {
        return Database::query(
            "SELECT DISTINCT category FROM contestants WHERE category IS NOT NULL AND category != '' ORDER BY category"
        )->fetchAll(PDO::FETCH_COLUMN);
    }
}
