<?php
/**
 * Standalone test runner: php tests/run.php
 */
require __DIR__ . '/../app/Services/ScoringEngine.php';

$failed = 0;
function assert_true($cond, string $msg): void
{
    global $failed;
    if ($cond) {
        echo "OK  $msg\n";
    } else {
        echo "FAIL  $msg\n";
        $failed++;
    }
}

$criteria = [
    ['id' => 1, 'weight' => 50],
    ['id' => 2, 'weight' => 50],
];
assert_true(ScoringEngine::weightsValid($criteria), 'weights total 100');
assert_true(ScoringEngine::weightsTotal([['weight' => 40], ['weight' => 59.9]]) !== 100.0, 'invalid weights detected');

$scores = [
    ['judge_id' => 1, 'contestant_id' => 10, 'criteria_id' => 1, 'score_value' => 80],
    ['judge_id' => 1, 'contestant_id' => 10, 'criteria_id' => 2, 'score_value' => 90],
    ['judge_id' => 2, 'contestant_id' => 10, 'criteria_id' => 1, 'score_value' => 70],
    ['judge_id' => 2, 'contestant_id' => 10, 'criteria_id' => 2, 'score_value' => 80],
];
$t = ScoringEngine::contestantTotals($criteria, $scores, false);
assert_true(abs($t[10]['average'] - 80.0) < 0.01, 'simple average of two judges is 80');
assert_true(abs($t[10]['score_sum'] - 160.0) < 0.01, 'sum of two judge totals is 160');

$scores3 = $scores;
$scores3[] = ['judge_id' => 3, 'contestant_id' => 10, 'criteria_id' => 1, 'score_value' => 100];
$scores3[] = ['judge_id' => 3, 'contestant_id' => 10, 'criteria_id' => 2, 'score_value' => 100];
$drop = ScoringEngine::contestantTotals($criteria, $scores3, true);
// judge totals: j1=85, j2=75, j3=100 → drop 75 and 100 → 85
assert_true(abs($drop[10]['average'] - 85.0) < 0.01, 'drop high/low keeps middle judge total');
assert_true(count($drop[10]['dropped_judge_ids']) === 2, 'two judges dropped');

$ranked = ScoringEngine::rank([
    1 => ['average' => 90],
    2 => ['average' => 88],
    3 => ['average' => 90],
]);
assert_true($ranked[1]['rank'] === 1 && $ranked[3]['rank'] === 1, 'tied scores share rank 1');
assert_true($ranked[2]['rank'] === 3, 'next rank after tie is 3');

assert_true(ScoringEngine::validateScore(85.5, 1, 100), 'decimal score in range');
assert_true(!ScoringEngine::validateScore(0, 1, 100), 'below min rejected');
assert_true(!ScoringEngine::validateScore(100.1, 1, 100), 'above max rejected');

$judgeTotal = ScoringEngine::judgeTotalForContestant($criteria, [
    ['criteria_id' => 1, 'score_value' => 80],
    ['criteria_id' => 2, 'score_value' => 100],
]);
assert_true(abs($judgeTotal - 90.0) < 0.01, 'weighted judge total');

require_once __DIR__ . '/../app/Models/Contestant.php';
assert_true(Contestant::normalizeDivision('exclusive') === 'Exclusive', 'exclusive category');
assert_true(Contestant::divisionKey('Open') === 'open', 'open division key');
assert_true(Contestant::divisionKey('Vocal') === 'open', 'legacy category maps to open');

$buckets = ScoringEngine::distributionBuckets([5, 15, 25]);
assert_true($buckets['0–10'] === 1 && $buckets['10–20'] === 1, 'distribution buckets');

echo $failed ? "\n$failed test(s) failed\n" : "\nAll tests passed\n";
exit($failed ? 1 : 0);
