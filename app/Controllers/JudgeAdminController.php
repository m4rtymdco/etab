<?php

class JudgeAdminController
{
    public function index(): void
    {
        require_admin();
        $judges = User::judges();
        $metrics = [];
        foreach ($judges as $j) {
            $metrics[$j['id']] = ResultsService::judgeMetrics((int) $j['id']);
        }
        View::render('admin/judges/index', [
            'title' => 'Judges',
            'judges' => $judges,
            'metrics' => $metrics,
        ]);
    }

    public function create(): void
    {
        require_admin();
        View::render('admin/judges/form', [
            'title' => 'Add judge',
            'judge' => null,
            'events' => Event::all(),
            'assigned' => [],
        ]);
    }

    public function store(): void
    {
        require_admin();
        verify_csrf();
        if (User::findByEmail($_POST['email'] ?? '')) {
            flash('error', 'Email already in use.');
            redirect('admin/judges/create');
        }
        $id = User::create([
            'name' => trim($_POST['name'] ?? ''),
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?: bin2hex(random_bytes(4)),
            'role' => 'judge',
            'phone' => trim($_POST['phone'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ]);
        foreach ($_POST['event_ids'] ?? [] as $eid) {
            Event::assignJudge((int) $eid, $id);
        }
        Audit::log(Auth::id(), 'judge_created', 'user', $id);
        flash('success', 'Judge created.');
        redirect('admin/judges');
    }

    public function edit(array $params): void
    {
        require_admin();
        $j = $this->findOrFail($params);
        $assigned = array_map(fn ($e) => (int) $e['id'], Event::forJudge((int) $j['id']));
        View::render('admin/judges/form', [
            'title' => 'Edit judge',
            'judge' => $j,
            'events' => Event::all(),
            'assigned' => $assigned,
            'metrics' => ResultsService::judgeMetrics((int) $j['id']),
        ]);
    }

    public function update(array $params): void
    {
        require_admin();
        verify_csrf();
        $j = $this->findOrFail($params);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => $_POST['email'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        User::update((int) $j['id'], $data);
        $selected = array_map('intval', $_POST['event_ids'] ?? []);
        $current = array_map(fn ($e) => (int) $e['id'], Event::forJudge((int) $j['id']));
        foreach (array_diff($selected, $current) as $eid) {
            Event::assignJudge($eid, (int) $j['id']);
        }
        foreach (array_diff($current, $selected) as $eid) {
            Event::unassignJudge($eid, (int) $j['id']);
        }
        Audit::log(Auth::id(), 'judge_updated', 'user', (int) $j['id']);
        flash('success', 'Judge updated.');
        redirect('admin/judges');
    }

    public function destroy(array $params): void
    {
        require_admin();
        verify_csrf();
        $j = $this->findOrFail($params);
        User::delete((int) $j['id']);
        Audit::log(Auth::id(), 'judge_deleted', 'user', (int) $j['id'], $j['email']);
        flash('success', 'Judge removed.');
        redirect('admin/judges');
    }

    private function findOrFail(array $params): array
    {
        $j = User::find((int) ($params['id'] ?? 0));
        if (!$j || $j['role'] !== 'judge') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found'], 'layouts/guest');
            exit;
        }
        return $j;
    }
}
