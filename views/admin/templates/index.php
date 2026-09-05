<div class="grid grid-2">
  <div class="card">
    <h2>New template</h2>
    <form method="post" action="<?= e(url('admin/templates')) ?>" id="template-form">
      <?= csrf_field() ?>
      <div class="page-head">
        <h3>Criteria</h3>
        <button class="btn" type="button" data-add-criterion>Add New Criterion</button>
      </div>
      <p class="muted">Weights across all criteria should total 100%. Total: <strong data-weight-total>0</strong>%</p>
      <div id="criteria-list"></div>
      <p class="muted" id="criteria-empty">No criteria yet. Click Add New Criterion.</p>
      <button class="btn btn-success" type="submit">Create template</button>
    </form>
  </div>
  <div>
    <?php foreach ($templates as $t): if(!$t) continue; ?>
      <div class="card" style="margin-bottom:1rem">
        <h3><?= e($t['name']) ?></h3>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Order</th>
                <th>Section</th>
                <th>Criterion name</th>
                <th>Description</th>
                <th>Max score</th>
                <th>Weight %</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($t['items'] as $it): ?>
              <tr>
                <td><?= e((string)($it['sort_order'] ?? '')) ?></td>
                <td><?= e($it['section'] ?? '') ?></td>
                <td><?= e($it['name']) ?></td>
                <td><?= e($it['description'] ?? '') ?></td>
                <td><?= e((string)($it['max_score'] ?? '100')) ?></td>
                <td><?= e($it['weight']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <form method="post" action="<?= e(url('admin/templates/'.$t['id'].'/delete')) ?>" data-confirm="Delete template?">
          <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Delete</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<template id="criterion-row-tpl">
  <div class="criterion-block">
    <div class="page-head">
      <strong>Criterion</strong>
      <button class="btn btn-sm btn-danger" type="button" data-remove-criterion>Remove</button>
    </div>
    <div class="form-group">
      <label>Section</label>
      <input name="items_section[]" placeholder="e.g. Performance, Technical">
    </div>
    <div class="form-group">
      <label>Criterion Name</label>
      <input name="items_name[]" required placeholder="Criterion name">
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea name="items_description[]" rows="2" placeholder="What judges should look for"></textarea>
    </div>
    <div class="grid grid-3">
      <div class="form-group">
        <label>Max Score</label>
        <input name="items_max_score[]" type="number" step="0.1" min="0.1" value="100" required>
      </div>
      <div class="form-group">
        <label>Weight (%)</label>
        <input name="items_weight[]" type="number" step="0.01" min="0" max="100" value="0" required data-weight>
      </div>
      <div class="form-group">
        <label>Display Order</label>
        <input name="items_sort_order[]" type="number" min="1" value="1" required data-order>
      </div>
    </div>
  </div>
</template>
<script src="<?= e(asset('js/templates.js')) ?>"></script>
