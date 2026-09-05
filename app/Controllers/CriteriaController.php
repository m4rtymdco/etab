<?php

class CriteriaController
{
    public function store(array $params): void
    {
        require_admin();
        verify_csrf();
        $eventId = (int) $params['id'];
        Criteria::create([
            'event_id' => $eventId,
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'section' => trim($_POST['section'] ?? '') ?: null,
            'max_score' => (float) ($_POST['max_score'] ?? 100),
            'weight' => (float) ($_POST['weight'] ?? 0),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        Audit::log(Auth::id(), 'criteria_created', 'event', $eventId);
        flash('success', 'Criterion added.');
        redirect('admin/events/' . $eventId);
    }

    public function update(array $params): void
    {
        require_admin();
        verify_csrf();
        $c = Criteria::find((int) $params['cid']);
        if (!$c) {
            redirect('admin/events');
        }
        Criteria::update((int) $c['id'], [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'section' => trim($_POST['section'] ?? '') ?: null,
            'max_score' => (float) ($_POST['max_score'] ?? 100),
            'weight' => (float) ($_POST['weight'] ?? 0),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        flash('success', 'Criterion updated.');
        redirect('admin/events/' . $c['event_id']);
    }

    public function destroy(array $params): void
    {
        require_admin();
        verify_csrf();
        $c = Criteria::find((int) $params['cid']);
        if ($c) {
            Criteria::delete((int) $c['id']);
            Audit::log(Auth::id(), 'criteria_deleted', 'criteria', (int) $c['id']);
            flash('success', 'Criterion deleted.');
            redirect('admin/events/' . $c['event_id']);
        }
        redirect('admin/events');
    }

    public function applyTemplate(array $params): void
    {
        require_admin();
        verify_csrf();
        $eventId = (int) $params['id'];
        Criteria::applyTemplate($eventId, (int) $_POST['template_id']);
        Audit::log(Auth::id(), 'criteria_template_applied', 'event', $eventId);
        flash('success', 'Template applied.');
        redirect('admin/events/' . $eventId);
    }

    public function saveTemplate(array $params): void
    {
        require_admin();
        verify_csrf();
        $eventId = (int) $params['id'];
        $name = trim($_POST['template_name'] ?? '') ?: ('Event ' . $eventId . ' template');
        CriteriaTemplate::fromEvent($eventId, $name);
        flash('success', 'Criteria saved as template.');
        redirect('admin/events/' . $eventId);
    }

    public function templates(): void
    {
        require_admin();
        View::render('admin/templates/index', [
            'title' => 'Criteria templates',
            'templates' => array_map(function ($t) {
                return CriteriaTemplate::find((int) $t['id']);
            }, CriteriaTemplate::all()),
        ]);
    }

    public function storeTemplate(): void
    {
        require_admin();
        verify_csrf();
        $items = [];
        foreach ($_POST['items_name'] ?? [] as $i => $name) {
            $items[] = [
                'name' => $name,
                'section' => $_POST['items_section'][$i] ?? '',
                'description' => $_POST['items_description'][$i] ?? '',
                'max_score' => $_POST['items_max_score'][$i] ?? 100,
                'weight' => $_POST['items_weight'][$i] ?? 0,
                'sort_order' => $_POST['items_sort_order'][$i] ?? $i,
            ];
        }
        $first = '';
        foreach ($items as $item) {
            if (!empty(trim((string) ($item['name'] ?? '')))) {
                $first = trim($item['name']);
                break;
            }
        }
        $autoName = $first !== '' ? $first : ('Template ' . date('M j, Y g:i A'));
        CriteriaTemplate::create($autoName, null, $items);
        flash('success', 'Template created.');
        redirect('admin/templates');
    }

    public function destroyTemplate(array $params): void
    {
        require_admin();
        verify_csrf();
        CriteriaTemplate::delete((int) $params['id']);
        flash('success', 'Template deleted.');
        redirect('admin/templates');
    }
}
