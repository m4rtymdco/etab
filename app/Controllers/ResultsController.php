<?php

class ResultsController
{
    public function index(): void
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $category = trim($_GET['category'] ?? '');
        $events = Event::all();
        if (!$eventId && $events) {
            $eventId = (int) $events[0]['id'];
        }
        $standings = $eventId ? ResultsService::standings($eventId, $round, $category ?: null) : ['rows' => [], 'criteria' => [], 'event' => null, 'groups' => []];
        View::render('admin/results/index', [
            'title' => 'Results',
            'events' => $events,
            'event_id' => $eventId,
            'round' => $round,
            'category' => $category,
            'standings' => $standings,
            'event_judges' => $eventId ? Event::judges($eventId) : [],
            'progress' => $eventId ? ResultsService::progress($eventId, $round) : null,
        ]);
    }

    public function live(array $params): void
    {
        require_auth();
        $event = Event::find((int) $params['id']);
        if (!$event) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found'], 'layouts/guest');
            return;
        }
        if (!is_admin() && !(int) $event['results_published']) {
            flash('error', 'Results are not published yet.');
            redirect(is_admin() ? 'admin' : 'judge');
        }
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $standings = ResultsService::standings((int) $event['id'], $round);
        View::render('results/live', [
            'title' => 'Live standings — ' . $event['name'],
            'event' => $event,
            'round' => $round,
            'standings' => $standings,
            'poll_url' => url('api/events/' . $event['id'] . '/standings?round=' . $round),
            'interval' => config('poll_interval_ms', 4000),
        ], 'layouts/wide');
    }

    public function exportCsv(): void
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $standings = ResultsService::standings($eventId, $round);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="results-event-' . $eventId . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $head = ['Category', 'Rank', 'Contestant', 'Total', '%'];
        fputcsv($out, $head);
        foreach ($standings['groups'] ?? [] as $group) {
            foreach ($group['rows'] as $r) {
                fputcsv($out, [
                    $group['label'],
                    $r['rank'],
                    $r['name'],
                    $r['score_sum'] ?? 0,
                    number_format((float) $r['average'], 1) . '%',
                ]);
            }
        }
        fclose($out);
        exit;
    }

    public function exportExcel(): void
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $standings = ResultsService::standings($eventId, $round);
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="results-event-' . $eventId . '.xls"');
        echo "<table border='1'><tr><th>Category</th><th>Rank</th><th>Contestant</th><th>Total</th><th>%</th></tr>";
        foreach ($standings['groups'] ?? [] as $group) {
            foreach ($group['rows'] as $r) {
                echo '<tr><td>' . e($group['label']) . '</td><td>' . e((string) $r['rank']) . '</td><td>' . e($r['name']) . '</td>';
                echo '<td>' . e(format_score($r['score_sum'] ?? 0)) . '</td><td>' . e(format_pct($r['average'], 1)) . '</td></tr>';
            }
        }
        echo '</table>';
        exit;
    }

    public function printReport(): void
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $standings = ResultsService::standings($eventId, $round);
        View::render('admin/results/print', [
            'title' => 'Results report',
            'standings' => $standings,
            'round' => $round,
        ], 'layouts/print');
    }

    public function certificates(): void
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $standings = ResultsService::standings($eventId, $round);
        $winners = [];
        foreach ($standings['groups'] ?? [] as $group) {
            foreach ($group['rows'] as $r) {
                if ($r['rank'] !== null && $r['rank'] <= 3 && $r['judge_count'] > 0) {
                    $winners[] = $r;
                }
            }
        }
        View::render('admin/results/certificates', [
            'title' => 'Winner certificates',
            'standings' => $standings,
            'winners' => $winners,
            'round' => $round,
        ], 'layouts/print');
    }

    public function scoreSheet(array $params): void
    {
        require_admin();
        $event = Event::find((int) $params['id']);
        if (!$event) {
            redirect('admin/events');
        }
        View::render('admin/events/scoresheet', [
            'title' => 'Score sheet',
            'event' => $event,
            'criteria' => Criteria::forEvent((int) $event['id']),
            'contestants' => Contestant::forEvent((int) $event['id']),
            'judges' => Event::judges((int) $event['id']),
        ], 'layouts/print');
    }

    public function apiStandings(array $params): void
    {
        require_auth();
        $event = Event::find((int) $params['id']);
        if (!$event) {
            json_response(['error' => 'Not found'], 404);
        }
        if (!is_admin() && !(int) $event['results_published']) {
            json_response(['error' => 'Not published'], 403);
        }
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $standings = ResultsService::standings((int) $event['id'], $round);
        $mapRow = function ($r) use ($standings) {
            $breakdown = [];
            foreach ($standings['criteria'] as $c) {
                $breakdown[] = [
                    'criteria_id' => $c['id'],
                    'name' => $c['name'],
                    'avg' => $r['criteria_avg'][$c['id']]['raw_avg'] ?? null,
                ];
            }
            return [
                'id' => $r['id'],
                'name' => $r['name'],
                'category' => $r['category'],
                'division' => $r['division'] ?? Contestant::divisionKey($r['category'] ?? ''),
                'rank' => $r['rank'],
                'average' => $r['average'],
                'score_sum' => $r['score_sum'] ?? 0,
                'judge_count' => $r['judge_count'],
                'breakdown' => $breakdown,
            ];
        };
        json_response([
            'event' => ['id' => $event['id'], 'name' => $event['name'], 'published' => (bool) $event['results_published']],
            'round' => $round,
            'updated_at' => date(DATE_ATOM),
            'groups' => array_map(function ($g) use ($mapRow) {
                return [
                    'key' => $g['key'],
                    'label' => $g['label'],
                    'rows' => array_map($mapRow, $g['rows']),
                ];
            }, array_values($standings['groups'] ?? [])),
            'rows' => array_map($mapRow, $standings['rows']),
        ]);
    }
}
