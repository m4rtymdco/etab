<?php

class ResultsService
{
    public static function divisionLabels(): array
    {
        return [
            'exclusive' => 'Exclusive',
            'open' => 'Open',
        ];
    }

    public static function standings(int $eventId, int $round = 1, ?string $category = null): array
    {
        $event = Event::find($eventId);
        if (!$event) {
            return ['event' => null, 'criteria' => [], 'rows' => [], 'groups' => []];
        }
        $criteria = Criteria::forEvent($eventId);
        $contestants = Contestant::forEvent($eventId);
        $filterKey = $category ? Contestant::divisionKey($category) : null;
        if ($filterKey && $category !== '') {
            $contestants = array_values(array_filter(
                $contestants,
                fn ($c) => Contestant::divisionKey($c['category'] ?? '') === $filterKey
            ));
        }
        $scores = Score::forEvent($eventId, $round);
        $totals = ScoringEngine::contestantTotals($criteria, $scores, (bool) $event['drop_high_low']);

        $byDiv = ['exclusive' => [], 'open' => []];
        foreach ($contestants as $c) {
            $byDiv[Contestant::divisionKey($c['category'] ?? '')][] = $c;
        }

        $groups = [];
        $allRows = [];
        foreach (self::divisionLabels() as $key => $label) {
            $subset = [];
            foreach ($byDiv[$key] as $c) {
                $cid = (int) $c['id'];
                if (isset($totals[$cid])) {
                    $subset[$cid] = $totals[$cid];
                }
            }
            $ranked = ScoringEngine::rank($subset);
            $rows = [];
            foreach ($byDiv[$key] as $c) {
                $cid = (int) $c['id'];
                $t = $ranked[$cid] ?? [
                    'contestant_id' => $cid,
                    'average' => 0,
                    'score_sum' => 0,
                    'rank' => null,
                    'judge_count' => 0,
                    'judge_totals' => [],
                    'judge_criteria' => [],
                    'dropped_judge_ids' => [],
                    'criteria_avg' => [],
                ];
                $rows[] = array_merge($c, $t, ['division' => $key, 'category' => $label]);
            }
            usort($rows, [self::class, 'sortStandingRows']);
            $groups[$key] = [
                'key' => $key,
                'label' => $label,
                'rows' => $rows,
            ];
            $allRows = array_merge($allRows, $rows);
        }

        return [
            'event' => $event,
            'criteria' => $criteria,
            'rows' => $allRows,
            'groups' => $groups,
            'weights_ok' => ScoringEngine::weightsValid($criteria),
        ];
    }

    private static function sortStandingRows(array $a, array $b): int
    {
        if ($a['rank'] === null && $b['rank'] === null) {
            return strcmp($a['name'], $b['name']);
        }
        if ($a['rank'] === null) {
            return 1;
        }
        if ($b['rank'] === null) {
            return -1;
        }
        return $a['rank'] <=> $b['rank'] ?: strcmp($a['name'], $b['name']);
    }

    public static function progress(int $eventId, int $round = 1): array
    {
        $judges = Event::judges($eventId);
        $contestants = Contestant::forEvent($eventId);
        $expected = count($judges) * count($contestants);
        $donePairs = Database::query(
            'SELECT COUNT(DISTINCT CONCAT(judge_id, "-", contestant_id)) c
             FROM scores WHERE event_id=? AND round=?',
            [$eventId, $round]
        )->fetch();
        $done = (int) ($donePairs['c'] ?? 0);
        return [
            'judges' => count($judges),
            'contestants' => count($contestants),
            'expected_sheets' => $expected,
            'completed_sheets' => $done,
            'percent' => $expected ? round(100 * $done / $expected, 1) : 0,
        ];
    }

    public static function judgeMetrics(int $judgeId): array
    {
        $row = Database::query(
            'SELECT COUNT(DISTINCT CONCAT(event_id,"-",contestant_id,"-",round)) sheets,
                    COUNT(*) scores_count,
                    AVG(score_value) avg_score,
                    MIN(score_value) min_score,
                    MAX(score_value) max_score
             FROM scores WHERE judge_id=?',
            [$judgeId]
        )->fetch();
        $events = (int) Database::query(
            'SELECT COUNT(*) c FROM judge_assignments WHERE judge_id=?',
            [$judgeId]
        )->fetch()['c'];
        return [
            'assigned_events' => $events,
            'sheets' => (int) ($row['sheets'] ?? 0),
            'scores_count' => (int) ($row['scores_count'] ?? 0),
            'avg_score' => $row['avg_score'] !== null ? round((float) $row['avg_score'], 2) : null,
            'min_score' => $row['min_score'] !== null ? (float) $row['min_score'] : null,
            'max_score' => $row['max_score'] !== null ? (float) $row['max_score'] : null,
        ];
    }
}
