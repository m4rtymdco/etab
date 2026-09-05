<?php

class ContestantController
{
    public function index(): void
    {
        require_admin();
        View::render('admin/contestants/index', [
            'title' => 'Contestants',
            'contestants' => Contestant::all(true),
            'events' => Event::all(),
        ]);
    }

    public function create(): void
    {
        require_admin();
        View::render('admin/contestants/form', [
            'title' => 'Add contestant',
            'contestant' => null,
            'events' => Event::all(),
            'assigned_events' => [],
        ]);
    }

    public function store(): void
    {
        require_admin();
        verify_csrf();
        $photo = $this->handleUpload();
        $id = Contestant::create([
            'name' => trim($_POST['name'] ?? ''),
            'category' => Contestant::normalizeDivision($_POST['category'] ?? 'open'),
            'photo_url' => $photo,
            'status' => $_POST['status'] ?? 'active',
            'notes' => trim($_POST['notes'] ?? ''),
        ]);
        foreach ($_POST['event_ids'] ?? [] as $eid) {
            Contestant::assignToEvent($id, (int) $eid);
        }
        Audit::log(Auth::id(), 'contestant_created', 'contestant', $id);
        flash('success', 'Contestant added.');
        redirect('admin/contestants');
    }

    public function edit(array $params): void
    {
        require_admin();
        $c = $this->findOrFail($params);
        $assigned = array_map(fn ($e) => (int) $e['id'], Contestant::events((int) $c['id']));
        View::render('admin/contestants/form', [
            'title' => 'Edit contestant',
            'contestant' => $c,
            'events' => Event::all(),
            'assigned_events' => $assigned,
        ]);
    }

    public function update(array $params): void
    {
        require_admin();
        verify_csrf();
        $c = $this->findOrFail($params);
        $photo = $this->handleUpload() ?: $c['photo_url'];
        Contestant::update((int) $c['id'], [
            'name' => trim($_POST['name'] ?? ''),
            'category' => Contestant::normalizeDivision($_POST['category'] ?? 'open'),
            'photo_url' => $photo,
            'status' => $_POST['status'] ?? 'active',
            'notes' => trim($_POST['notes'] ?? ''),
        ]);
        $selected = array_map('intval', $_POST['event_ids'] ?? []);
        $current = array_map(fn ($e) => (int) $e['id'], Contestant::events((int) $c['id']));
        foreach (array_diff($selected, $current) as $eid) {
            Contestant::assignToEvent((int) $c['id'], $eid);
        }
        foreach (array_diff($current, $selected) as $eid) {
            Contestant::unassignFromEvent((int) $c['id'], $eid);
        }
        Audit::log(Auth::id(), 'contestant_updated', 'contestant', (int) $c['id']);
        flash('success', 'Contestant updated.');
        redirect('admin/contestants');
    }

    public function archive(array $params): void
    {
        require_admin();
        verify_csrf();
        $c = $this->findOrFail($params);
        Contestant::archive((int) $c['id']);
        Audit::log(Auth::id(), 'contestant_archived', 'contestant', (int) $c['id']);
        flash('success', 'Contestant archived.');
        redirect('admin/contestants');
    }

    public function destroy(array $params): void
    {
        require_admin();
        verify_csrf();
        $c = $this->findOrFail($params);
        Contestant::delete((int) $c['id']);
        Audit::log(Auth::id(), 'contestant_deleted', 'contestant', (int) $c['id'], $c['name']);
        flash('success', 'Contestant deleted.');
        redirect('admin/contestants');
    }

    public function exportCsv(): void
    {
        require_admin();
        $rows = Contestant::all(true);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="contestants.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['id', 'name', 'category', 'status', 'notes', 'events']);
        foreach ($rows as $r) {
            $events = array_map(fn ($e) => $e['name'], Contestant::events((int) $r['id']));
            fputcsv($out, [$r['id'], $r['name'], $r['category'], $r['status'], $r['notes'], implode('; ', $events)]);
        }
        fclose($out);
        exit;
    }

    public function importCsv(): void
    {
        require_admin();
        verify_csrf();
        if (empty($_FILES['csv']['tmp_name'])) {
            flash('error', 'Please choose a CSV file.');
            redirect('admin/contestants');
        }
        $fh = fopen($_FILES['csv']['tmp_name'], 'r');
        $header = fgetcsv($fh);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header ?: []);
        $imported = 0;
        $eventId = (int) ($_POST['event_id'] ?? 0);
        while (($row = fgetcsv($fh)) !== false) {
            $data = array_combine($header, array_pad($row, count($header), ''));
            if (!$data || empty($data['name'])) {
                continue;
            }
            $id = Contestant::create([
                'name' => trim($data['name']),
                'category' => Contestant::normalizeDivision($data['category'] ?? 'open'),
                'status' => in_array(($data['status'] ?? 'active'), ['active', 'archived'], true) ? $data['status'] : 'active',
                'notes' => trim($data['notes'] ?? ''),
            ]);
            $eid = $eventId ?: (int) ($data['event_id'] ?? 0);
            if ($eid) {
                Contestant::assignToEvent($id, $eid, $data['entry_number'] ?? null);
            }
            $imported++;
        }
        fclose($fh);
        Audit::log(Auth::id(), 'contestants_imported', 'contestant', null, ['count' => $imported]);
        flash('success', "Imported {$imported} contestants.");
        redirect('admin/contestants');
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['photo']['tmp_name']) || !is_uploaded_file($_FILES['photo']['tmp_name'])) {
            return null;
        }
        $dir = config('upload_dir');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return null;
        }
        $name = 'c_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $name);
        return config('upload_url') . '/' . $name;
    }

    private function findOrFail(array $params): array
    {
        $c = Contestant::find((int) ($params['id'] ?? 0));
        if (!$c) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found'], 'layouts/guest');
            exit;
        }
        return $c;
    }
}
