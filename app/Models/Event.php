<?php

class Event
{
    public static function find(int $id): ?array
    {
        return Database::query('SELECT * FROM events WHERE id = ?', [$id])->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::query('SELECT * FROM events ORDER BY event_date DESC, id DESC')->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::query('SELECT COUNT(*) c FROM events')->fetch()['c'];
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO events (name, description, event_date, event_time, venue, status, drop_high_low, score_min, score_max, rounds, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['description'] ?? null,
                $data['event_date'] ?: null,
                $data['event_time'] ?: null,
                $data['venue'] ?? null,
                $data['status'] ?? 'upcoming',
                !empty($data['drop_high_low']) ? 1 : 0,
                $data['score_min'] ?? 1,
                $data['score_max'] ?? 100,
                max(1, (int) ($data['rounds'] ?? 1)),
                Auth::id(),
            ]
        );
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE events SET name=?, description=?, event_date=?, event_time=?, venue=?, status=?,
             drop_high_low=?, score_min=?, score_max=?, rounds=?, results_published=? WHERE id=?',
            [
                $data['name'],
                $data['description'] ?? null,
                $data['event_date'] ?: null,
                $data['event_time'] ?: null,
                $data['venue'] ?? null,
                $data['status'] ?? 'upcoming',
                !empty($data['drop_high_low']) ? 1 : 0,
                $data['score_min'] ?? 1,
                $data['score_max'] ?? 100,
                max(1, (int) ($data['rounds'] ?? 1)),
                !empty($data['results_published']) ? 1 : 0,
                $id,
            ]
        );
    }

    public static function setPublished(int $id, bool $published): void
    {
        Database::query('UPDATE events SET results_published = ? WHERE id = ?', [$published ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM events WHERE id = ?', [$id]);
    }

    public static function forJudge(int $judgeId): array
    {
        return Database::query(
            'SELECT e.* FROM events e
             INNER JOIN judge_assignments ja ON ja.event_id = e.id
             WHERE ja.judge_id = ?
             ORDER BY e.event_date DESC, e.id DESC',
            [$judgeId]
        )->fetchAll();
    }

    public static function judges(int $eventId): array
    {
        return Database::query(
            'SELECT u.* FROM users u
             INNER JOIN judge_assignments ja ON ja.judge_id = u.id
             WHERE ja.event_id = ?
             ORDER BY u.name',
            [$eventId]
        )->fetchAll();
    }

    public static function assignJudge(int $eventId, int $judgeId): void
    {
        Database::query(
            'INSERT IGNORE INTO judge_assignments (judge_id, event_id) VALUES (?, ?)',
            [$judgeId, $eventId]
        );
    }

    public static function unassignJudge(int $eventId, int $judgeId): void
    {
        Database::query(
            'DELETE FROM judge_assignments WHERE event_id = ? AND judge_id = ?',
            [$eventId, $judgeId]
        );
    }

    public static function isJudgeAssigned(int $eventId, int $judgeId): bool
    {
        return (bool) Database::query(
            'SELECT id FROM judge_assignments WHERE event_id = ? AND judge_id = ?',
            [$eventId, $judgeId]
        )->fetch();
    }
}
