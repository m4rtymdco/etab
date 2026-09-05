<?php

class Score
{
    public static function forJudgeContestant(int $judgeId, int $contestantId, int $eventId, int $round = 1): array
    {
        return Database::query(
            'SELECT * FROM scores WHERE judge_id=? AND contestant_id=? AND event_id=? AND round=?',
            [$judgeId, $contestantId, $eventId, $round]
        )->fetchAll();
    }

    public static function forEvent(int $eventId, int $round = 1): array
    {
        return Database::query(
            'SELECT * FROM scores WHERE event_id=? AND round=?',
            [$eventId, $round]
        )->fetchAll();
    }

    public static function forJudgeEvent(int $judgeId, int $eventId, int $round = 1): array
    {
        return Database::query(
            'SELECT * FROM scores WHERE judge_id=? AND event_id=? AND round=?',
            [$judgeId, $eventId, $round]
        )->fetchAll();
    }

    public static function upsertBatch(int $judgeId, int $contestantId, int $eventId, int $round, array $values, ?string $comments = null, bool $isOverride = false, ?int $overrideBy = null): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $first = true;
            foreach ($values as $criteriaId => $scoreValue) {
                $cmt = $first ? $comments : null;
                $first = false;
                Database::query(
                    'INSERT INTO scores (judge_id, contestant_id, criteria_id, event_id, round, score_value, comments, is_override, override_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE score_value=VALUES(score_value), comments=COALESCE(VALUES(comments), comments),
                       is_override=VALUES(is_override), override_by=VALUES(override_by), updated_at=NOW()',
                    [
                        $judgeId,
                        $contestantId,
                        (int) $criteriaId,
                        $eventId,
                        $round,
                        $scoreValue,
                        $cmt,
                        $isOverride ? 1 : 0,
                        $overrideBy,
                    ]
                );
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function isSubmitted(int $judgeId, int $contestantId, int $eventId, int $round = 1): bool
    {
        return (bool) Database::query(
            'SELECT id FROM scores WHERE judge_id=? AND contestant_id=? AND event_id=? AND round=? LIMIT 1',
            [$judgeId, $contestantId, $eventId, $round]
        )->fetch();
    }

    public static function recent(int $limit = 10): array
    {
        return Database::query(
            'SELECT s.*, u.name AS judge_name, c.name AS contestant_name, e.name AS event_name, cr.name AS criteria_name
             FROM scores s
             JOIN users u ON u.id = s.judge_id
             JOIN contestants c ON c.id = s.contestant_id
             JOIN events e ON e.id = s.event_id
             JOIN criteria cr ON cr.id = s.criteria_id
             ORDER BY s.updated_at DESC
             LIMIT ' . (int) $limit
        )->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::query('SELECT COUNT(*) c FROM scores')->fetch()['c'];
    }

    public static function saveDraft(int $judgeId, int $contestantId, int $eventId, int $round, array $payload): void
    {
        Database::query(
            'INSERT INTO score_drafts (judge_id, contestant_id, event_id, round, payload)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE payload=VALUES(payload), updated_at=NOW()',
            [$judgeId, $contestantId, $eventId, $round, json_encode($payload)]
        );
    }

    public static function getDraft(int $judgeId, int $contestantId, int $eventId, int $round): ?array
    {
        $row = Database::query(
            'SELECT payload FROM score_drafts WHERE judge_id=? AND contestant_id=? AND event_id=? AND round=?',
            [$judgeId, $contestantId, $eventId, $round]
        )->fetch();
        if (!$row) {
            return null;
        }
        $data = json_decode($row['payload'], true);
        return is_array($data) ? $data : null;
    }

    public static function clearDraft(int $judgeId, int $contestantId, int $eventId, int $round): void
    {
        Database::query(
            'DELETE FROM score_drafts WHERE judge_id=? AND contestant_id=? AND event_id=? AND round=?',
            [$judgeId, $contestantId, $eventId, $round]
        );
    }
}
