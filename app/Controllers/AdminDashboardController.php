<?php

class AdminDashboardController
{
    public function index(): void
    {
        require_admin();
        $events = Event::all();
        $progress = [];
        foreach ($events as $e) {
            $progress[$e['id']] = ResultsService::progress((int) $e['id']);
        }
        View::render('admin/dashboard', [
            'title' => 'Admin dashboard',
            'events' => $events,
            'progress' => $progress,
            'stats' => [
                'events' => Event::count(),
                'contestants' => Contestant::count(),
                'judges' => User::countJudges(),
                'scores' => Score::count(),
            ],
            'recent' => Score::recent(12),
        ]);
    }

    public function analytics(): void
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $events = Event::all();
        if (!$eventId && $events) {
            $eventId = (int) $events[0]['id'];
        }
        $standings = $eventId ? ResultsService::standings($eventId) : ['rows' => [], 'criteria' => [], 'event' => null];
        $averages = array_map(fn ($r) => (float) $r['average'], $standings['rows']);
        $distribution = ScoringEngine::distributionBuckets($averages);

        $judgePatterns = [];
        if ($eventId) {
            $judgePatterns = Database::query(
                'SELECT u.id, u.name, AVG(s.score_value) avg_score, COUNT(*) n,
                        STDDEV_POP(s.score_value) stdev
                 FROM scores s JOIN users u ON u.id = s.judge_id
                 WHERE s.event_id = ?
                 GROUP BY u.id, u.name
                 ORDER BY u.name',
                [$eventId]
            )->fetchAll();
        }

        View::render('admin/analytics', [
            'title' => 'Analytics',
            'events' => $events,
            'event_id' => $eventId,
            'standings' => $standings,
            'distribution' => $distribution,
            'judge_patterns' => $judgePatterns,
        ]);
    }
}
