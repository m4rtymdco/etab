<?php

class EventController
{
    public function index(): void
    {
        require_admin();
        View::render('admin/events/index', [
            'title' => 'Events',
            'events' => Event::all(),
        ]);
    }

    public function create(): void
    {
        require_admin();
        View::render('admin/events/form', [
            'title' => 'Create event',
            'event' => null,
            'judges' => User::judges(),
            'assigned' => [],
        ]);
    }

    public function store(): void
    {
        require_admin();
        verify_csrf();
        $id = Event::create($this->payload());
        $this->syncJudges($id);
        Audit::log(Auth::id(), 'event_created', 'event', $id, $_POST['name'] ?? '');
        flash('success', 'Event created.');
        redirect('admin/events/' . $id);
    }

    public function show(array $params): void
    {
        require_admin();
        $event = $this->findOrFail($params);
        $criteria = Criteria::forEvent((int) $event['id']);
        View::render('admin/events/show', [
            'title' => $event['name'],
            'event' => $event,
            'criteria' => $criteria,
            'weights_total' => ScoringEngine::weightsTotal($criteria),
            'contestants' => Contestant::forEvent((int) $event['id'], true),
            'all_contestants' => Contestant::all(),
            'judges' => Event::judges((int) $event['id']),
            'all_judges' => User::judges(),
            'templates' => CriteriaTemplate::all(),
            'progress' => ResultsService::progress((int) $event['id']),
        ]);
    }

    public function edit(array $params): void
    {
        require_admin();
        $event = $this->findOrFail($params);
        $assigned = array_map(fn ($j) => (int) $j['id'], Event::judges((int) $event['id']));
        View::render('admin/events/form', [
            'title' => 'Edit event',
            'event' => $event,
            'judges' => User::judges(),
            'assigned' => $assigned,
        ]);
    }

    public function update(array $params): void
    {
        require_admin();
        verify_csrf();
        $event = $this->findOrFail($params);
        Event::update((int) $event['id'], $this->payload());
        $this->syncJudges((int) $event['id']);
        Audit::log(Auth::id(), 'event_updated', 'event', (int) $event['id']);
        flash('success', 'Event updated.');
        redirect('admin/events/' . $event['id']);
    }

    public function destroy(array $params): void
    {
        require_admin();
        verify_csrf();
        $event = $this->findOrFail($params);
        Event::delete((int) $event['id']);
        Audit::log(Auth::id(), 'event_deleted', 'event', (int) $event['id'], $event['name']);
        flash('success', 'Event deleted.');
        redirect('admin/events');
    }

    public function publish(array $params): void
    {
        require_admin();
        verify_csrf();
        $event = $this->findOrFail($params);
        $pub = !empty($_POST['published']);
        Event::setPublished((int) $event['id'], $pub);
        Audit::log(Auth::id(), $pub ? 'results_published' : 'results_unpublished', 'event', (int) $event['id']);
        flash('success', $pub ? 'Results published.' : 'Results hidden.');
        redirect('admin/events/' . $event['id']);
    }

    public function assignContestant(array $params): void
    {
        require_admin();
        verify_csrf();
        $event = $this->findOrFail($params);
        Contestant::assignToEvent((int) $_POST['contestant_id'], (int) $event['id'], $_POST['entry_number'] ?? null);
        flash('success', 'Contestant assigned.');
        redirect('admin/events/' . $event['id']);
    }

    public function unassignContestant(array $params): void
    {
        require_admin();
        verify_csrf();
        $event = $this->findOrFail($params);
        Contestant::unassignFromEvent((int) $_POST['contestant_id'], (int) $event['id']);
        flash('success', 'Contestant removed from event.');
        redirect('admin/events/' . $event['id']);
    }

    private function payload(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'event_date' => $_POST['event_date'] ?? null,
            'event_time' => $_POST['event_time'] ?? null,
            'venue' => trim($_POST['venue'] ?? ''),
            'status' => $_POST['status'] ?? 'upcoming',
            'drop_high_low' => !empty($_POST['drop_high_low']),
            'score_min' => (float) ($_POST['score_min'] ?? 1),
            'score_max' => (float) ($_POST['score_max'] ?? 100),
            'rounds' => (int) ($_POST['rounds'] ?? 1),
            'results_published' => !empty($_POST['results_published']),
        ];
    }

    private function syncJudges(int $eventId): void
    {
        $selected = array_map('intval', $_POST['judge_ids'] ?? []);
        $current = array_map(fn ($j) => (int) $j['id'], Event::judges($eventId));
        foreach (array_diff($selected, $current) as $jid) {
            Event::assignJudge($eventId, $jid);
        }
        foreach (array_diff($current, $selected) as $jid) {
            Event::unassignJudge($eventId, $jid);
        }
    }

    private function findOrFail(array $params): array
    {
        $event = Event::find((int) ($params['id'] ?? 0));
        if (!$event) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found'], 'layouts/guest');
            exit;
        }
        return $event;
    }
}
