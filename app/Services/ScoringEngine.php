<?php

/**
 * Weighted multi-judge scoring with optional Olympic-style drop high/low.
 */
class ScoringEngine
{
    /**
     * @param array $criteria [['id'=>int,'weight'=>float], ...]
     * @param array $scores rows with judge_id, contestant_id, criteria_id, score_value
     */
    public static function contestantTotals(array $criteria, array $scores, bool $dropHighLow = false): array
    {
        $weights = [];
        foreach ($criteria as $c) {
            $weights[(int) $c['id']] = (float) $c['weight'];
        }

        $byContestantJudge = [];
        foreach ($scores as $row) {
            $cid = (int) $row['contestant_id'];
            $jid = (int) $row['judge_id'];
            $crid = (int) $row['criteria_id'];
            if (!isset($weights[$crid])) {
                continue;
            }
            if (!isset($byContestantJudge[$cid][$jid])) {
                $byContestantJudge[$cid][$jid] = [
                    'total' => 0.0,
                    'by_criteria' => [],
                    'comments' => [],
                ];
            }
            $val = (float) $row['score_value'];
            $weighted = $val * ($weights[$crid] / 100);
            $byContestantJudge[$cid][$jid]['total'] += $weighted;
            $byContestantJudge[$cid][$jid]['by_criteria'][$crid] = $val;
            if (!empty($row['comments'])) {
                $byContestantJudge[$cid][$jid]['comments'][] = $row['comments'];
            }
        }

        $results = [];
        foreach ($byContestantJudge as $cid => $judges) {
            $totals = [];
            $judgeCriteria = [];
            $criteriaSums = [];
            $criteriaCounts = [];
            foreach ($judges as $jid => $data) {
                $totals[$jid] = $data['total'];
                $judgeCriteria[$jid] = $data['by_criteria'];
                foreach ($data['by_criteria'] as $crid => $val) {
                    $criteriaSums[$crid] = ($criteriaSums[$crid] ?? 0) + $val;
                    $criteriaCounts[$crid] = ($criteriaCounts[$crid] ?? 0) + 1;
                }
            }

            $used = $totals;
            $dropped = [];
            if ($dropHighLow && count($used) >= 3) {
                asort($used);
                $keys = array_keys($used);
                $dropped[] = $keys[0];
                $dropped[] = $keys[count($keys) - 1];
                unset($used[$keys[0]], $used[$keys[count($keys) - 1]]);
            }

            $sum = array_sum($used);
            $avg = count($used) ? $sum / count($used) : 0.0;
            $criteriaAvg = [];
            foreach ($weights as $crid => $w) {
                $cnt = $criteriaCounts[$crid] ?? 0;
                $rawAvg = $cnt ? ($criteriaSums[$crid] / $cnt) : 0.0;
                $criteriaAvg[$crid] = [
                    'raw_avg' => $rawAvg,
                    'weighted' => $rawAvg * ($w / 100),
                    'weight' => $w,
                ];
            }

            $results[$cid] = [
                'contestant_id' => $cid,
                'judge_totals' => $totals,
                'judge_criteria' => $judgeCriteria,
                'dropped_judge_ids' => $dropped,
                'score_sum' => round($sum, 2),
                'average' => round($avg, 2),
                'judge_count' => count($totals),
                'criteria_avg' => $criteriaAvg,
            ];
        }

        return $results;
    }

    public static function rank(array $totals): array
    {
        uasort($totals, function ($a, $b) {
            return $b['average'] <=> $a['average'];
        });
        $rank = 0;
        $pos = 0;
        $prev = null;
        foreach ($totals as $cid => &$row) {
            $pos++;
            if ($prev === null || $row['average'] < $prev) {
                $rank = $pos;
            }
            $row['rank'] = $rank;
            $prev = $row['average'];
        }
        unset($row);
        return $totals;
    }

    public static function judgeTotalForContestant(array $criteria, array $scores): float
    {
        $weights = [];
        foreach ($criteria as $c) {
            $weights[(int) $c['id']] = (float) $c['weight'];
        }
        $total = 0.0;
        foreach ($scores as $row) {
            $crid = (int) $row['criteria_id'];
            if (!isset($weights[$crid])) {
                continue;
            }
            $total += (float) $row['score_value'] * ($weights[$crid] / 100);
        }
        return round($total, 2);
    }

    public static function weightsTotal(array $criteria): float
    {
        $sum = 0.0;
        foreach ($criteria as $c) {
            $sum += (float) $c['weight'];
        }
        return round($sum, 2);
    }

    public static function weightsValid(array $criteria): bool
    {
        return abs(self::weightsTotal($criteria) - 100.0) < 0.01;
    }

    public static function validateScore(float $value, float $min, float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    public static function distributionBuckets(array $averages, int $bucketSize = 10): array
    {
        $buckets = [];
        for ($i = 0; $i < 100; $i += $bucketSize) {
            $label = $i . '–' . ($i + $bucketSize);
            $buckets[$label] = 0;
        }
        foreach ($averages as $avg) {
            $idx = min(9, (int) floor($avg / $bucketSize));
            $start = $idx * $bucketSize;
            $label = $start . '–' . ($start + $bucketSize);
            if (isset($buckets[$label])) {
                $buckets[$label]++;
            }
        }
        return $buckets;
    }
}
