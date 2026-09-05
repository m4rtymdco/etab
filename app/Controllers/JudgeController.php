<?php

class JudgeController
{
    public function dashboard(): void
    {
        require_auth();
        if (is_admin()) {
            redirect('admin');
        }
        $events = Event::forJudge(Auth::id());
        $tasks = [];
        $completed = 0;
        $pending = 0;
        foreach ($events as $e) {
            $contestants = Contestant::forEvent((int) $e['id']);
            $scores = Score::forJudgeEvent(Auth::id(), (int) $e['id']);
            $scoredIds = array_unique(array_map(fn ($s) => (int) $s['contestant_id'], $scores));
            $done = count($scoredIds);
            $total = count($contestants);
            $completed += $done;
            $pending += max(0, $total - $done);
            $tasks[] = [
                'event' => $e,
                'total' => $total,
                'done' => $done,
            ];
        }
        View::render('judge/dashboard', [
            'title' => 'Judge dashboard',
            'events' => $events,
            'tasks' => $tasks,
            'completed' => $completed,
            'pending' => $pending,
        ]);
    }

    public function event(array $params): void
    {
        require_auth();
        $event = $this->assignedEvent($params);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        $contestants = Contestant::forEvent((int) $event['id']);
        $scores = Score::forJudgeEvent(Auth::id(), (int) $event['id'], $round);
        $byC = [];
        foreach ($scores as $s) {
            $byC[(int) $s['contestant_id']][] = $s;
        }
        $criteria = Criteria::forEvent((int) $event['id']);
        $rows = [];
        foreach ($contestants as $c) {
            $cs = $byC[(int) $c['id']] ?? [];
            $rows[] = [
                'contestant' => $c,
                'submitted' => !empty($cs),
                'total' => $cs ? ScoringEngine::judgeTotalForContestant($criteria, $cs) : null,
            ];
        }
        $cid = (int) ($_GET['cid'] ?? 0);
        if (!$cid && $contestants) {
            $cid = (int) $contestants[0]['id'];
        }
        $contestant = $cid ? Contestant::find($cid) : null;
        $map = [];
        $comments = '';
        $submitted = false;
        $draft = null;
        if ($contestant) {
            $existing = Score::forJudgeContestant(Auth::id(), (int) $contestant['id'], (int) $event['id'], $round);
            $submitted = !empty($existing);
            foreach ($existing as $s) {
                $map[$s['criteria_id']] = $s['score_value'];
                if ($s['comments']) {
                    $comments = $s['comments'];
                }
            }
            $draft = $submitted ? null : Score::getDraft(Auth::id(), (int) $contestant['id'], (int) $event['id'], $round);
        }
        $editing = $submitted && (string) ($_GET['edit'] ?? '') === '1';
        $locked = $submitted && !$editing;
        View::render('judge/event', [
            'title' => $event['name'],
            'content_class' => 'score-content',
            'event' => $event,
            'round' => $round,
            'rows' => $rows,
            'criteria' => $criteria,
            'contestant' => $contestant,
            'submitted' => $submitted,
            'locked' => $locked,
            'values' => $map,
            'comments' => $comments,
            'draft' => $draft,
        ]);
    }

    public function scoreForm(array $params): void
    {
        require_auth();
        $event = $this->assignedEvent($params);
        $round = max(1, (int) ($_GET['round'] ?? 1));
        redirect('judge/events/' . $event['id'] . '?cid=' . (int) $params['cid'] . '&round=' . $round);
    }

    public function submitScore(array $params): void
    {
        require_auth();
        verify_csrf();
        $event = $this->assignedEvent($params);
        $contestantId = (int) $params['cid'];
        $round = max(1, (int) ($_POST['round'] ?? 1));
        $min = 0;
        $eventMax = (float) $event['score_max'] ?: 100;
        $criteria = Criteria::forEvent((int) $event['id']);
        if (!$criteria) {
            flash('error', 'This event has no scoring criteria yet. Ask an admin to add them.');
            redirect('judge/events/' . $event['id'] . '?cid=' . $contestantId . '&round=' . $round);
        }
        $values = [];
        foreach ($criteria as $c) {
            $val = (float) ($_POST['scores'][$c['id']] ?? 0);
            $cmax = (float) ($c['max_score'] ?? 0);
            if ($cmax <= 0) {
                $cmax = $eventMax;
            }
            if (!ScoringEngine::validateScore($val, $min, $cmax)) {
                flash('error', "Scores for {$c['name']} must be between {$min} and {$cmax}.");
                redirect('judge/events/' . $event['id'] . '?cid=' . $contestantId . '&round=' . $round);
            }
            $values[(int) $c['id']] = $val;
        }
        $updating = Score::isSubmitted(Auth::id(), $contestantId, (int) $event['id'], $round);
        Score::upsertBatch(Auth::id(), $contestantId, (int) $event['id'], $round, $values, trim($_POST['comments'] ?? ''));
        Score::clearDraft(Auth::id(), $contestantId, (int) $event['id'], $round);
        Audit::log(Auth::id(), $updating ? 'score_updated' : 'score_submitted', 'contestant', $contestantId, [
            'event_id' => $event['id'],
            'round' => $round,
        ]);
        flash('success', $updating ? 'Scores updated and published.' : 'Scores saved.');
        redirect('judge/events/' . $event['id'] . '?cid=' . $contestantId . '&round=' . $round);
    }

    public function saveDraft(array $params): void
    {
        require_auth();
        verify_csrf();
        $event = $this->assignedEvent($params);
        $body = request_json();
        $round = max(1, (int) ($body['round'] ?? 1));
        $cid = (int) $params['cid'];
        if (Score::isSubmitted(Auth::id(), $cid, (int) $event['id'], $round)) {
            json_response(['ok' => false, 'message' => 'Already submitted'], 409);
        }
        Score::saveDraft(Auth::id(), $cid, (int) $event['id'], $round, $body);
        json_response(['ok' => true, 'saved_at' => date(DATE_ATOM)]);
    }

    public function myScores(): void
    {
        require_auth();
        $events = Event::forJudge(Auth::id());
        $data = [];
        foreach ($events as $e) {
            $criteria = Criteria::forEvent((int) $e['id']);
            $contestants = Contestant::forEvent((int) $e['id']);
            $scores = Score::forJudgeEvent(Auth::id(), (int) $e['id']);
            $byC = [];
            foreach ($scores as $s) {
                $byC[(int) $s['contestant_id']][] = $s;
            }
            $rows = [];
            foreach ($contestants as $c) {
                if (empty($byC[(int) $c['id']])) {
                    continue;
                }
                $rows[] = [
                    'contestant' => $c,
                    'scores' => $byC[(int) $c['id']],
                    'total' => ScoringEngine::judgeTotalForContestant($criteria, $byC[(int) $c['id']]),
                ];
            }
            $data[] = ['event' => $e, 'criteria' => $criteria, 'rows' => $rows];
        }
        View::render('judge/scores', [
            'title' => 'My scores',
            'groups' => $data,
        ]);
    }

    public function profile(): void
    {
        require_auth();
        View::render('judge/profile', [
            'title' => 'Profile',
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(): void
    {
        require_auth();
        verify_csrf();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
        ];
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8 || $_POST['password'] !== ($_POST['password_confirm'] ?? '')) {
                flash('error', 'Passwords must match and be at least 8 characters.');
                redirect('judge/profile');
            }
            $data['password'] = $_POST['password'];
        }
        User::update(Auth::id(), $data);
        Audit::log(Auth::id(), 'profile_updated', 'user', Auth::id());
        flash('success', 'Profile updated.');
        redirect(is_admin() ? 'admin/profile' : 'judge/profile');
    }

    private function assignedEvent(array $params): array
    {
        $event = Event::find((int) ($params['id'] ?? 0));
        if (!$event) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found'], 'layouts/guest');
            exit;
        }
        if (!is_admin() && !Event::isJudgeAssigned((int) $event['id'], Auth::id())) {
            http_response_code(403);
            echo 'You are not assigned to this event.';
            exit;
        }
        return $event;
    }
}
