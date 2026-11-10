<?php require '../header.php';

$page = max(1, (int)($_GET['page'] ?? 1)); $perPage = 20; $offset = ($page - 1) * $perPage;
$q = trim($_GET['q'] ?? ''); $status = $_GET['status'] ?? ''; $cat = (int)($_GET['category'] ?? 0); $loc = (int)($_GET['location'] ?? 0);

$where = ["a.deleted_at IS NULL"]; $params = [];
if ($_SESSION['role'] === 'staff') { $where[] = "a.custodian_id = :uid"; $params[':uid'] = $_SESSION['user_id']; }
if ($q !== '') { $where[] = "(a.name LIKE :q OR a.asset_code LIKE :q)"; $params[':q'] = "%$q%"; }
if ($status !== '') { $where[] = "a.status = :st"; $params[':st'] = $status; }
if ($cat > 0) { $where[] = "a.category_id = :cat"; $params[':cat'] = $cat; }
if ($loc > 0) { $where[] = "a.location_id = :loc"; $params[':loc'] = $loc; }
$w = ' WHERE ' . implode(' AND ', $where);

$countSql = "SELECT COUNT(*) FROM assets a $w"; $cs = $pdo->prepare($countSql); $cs->execute($params);
$total = $cs->fetchColumn(); $pages = max(1, ceil($total / $perPage));

$sql = "SELECT a.*,c.name AS category,l.name AS location,u.name AS custodian FROM assets a LEFT JOIN categories c ON a.category_id=c.id LEFT JOIN locations l ON a.location_id=l.id LEFT JOIN users u ON a.custodian_id=u.id $w ORDER BY a.id DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$locs = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
<h2>📋 <?= __('assets') ?> (<?= $total ?>)</h2>
<div>
<?php if (isAdmin()): ?><a href="import.php" class="btn btn-outline-primary">📥 <?= __('import_csv') ?></a><a href="add.php" class="btn btn-success">➕ <?= __('add_asset') ?></a><?php endif; ?>
</div></div>

<form class="row g-2 mb-3" method="GET">
<div class="col-md-3"><input name="q" value="<?= e($q) ?>" class="form-control" placeholder="<?= __('search') ?>"></div>
<div class="col-md-2"><select name="status" class="form-select"><option value=""><?= __('all_status') ?></option>
<?php foreach (['available','in_use','under_service','lost','disposed'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-2"><select name="category" class="form-select"><option value=""><?= __('all_categories') ?></option>
<?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-2"><select name="location" class="form-select"><option value=""><?= __('all_locations') ?></option>
<?php foreach ($locs as $l): ?><option value="<?= $l['id'] ?>" <?= $loc==$l['id']?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-2"><button class="btn btn-primary w-100"><?= __('filter') ?></button></div>
<div class="col-md-1"><a href="index.php" class="btn btn-outline-secondary w-100"><?= __('reset') ?></a></div>
</form>

<?php if (isAdmin()): ?>
<form method="POST" action="bulk_action.php" id="bulkForm">
<?= csrf_field() ?>
<div class="card mb-2 d-none" id="bulkToolbar">
    <div class="card-body py-2 d-flex align-items-center gap-2 bg-light">
        <span class="fw-bold text-primary"><span id="selectedCount">0</span> selected</span>
        <select name="action" class="form-select form-select-sm" style="width:200px" required>
            <option value="">-- Bulk Action --</option>
            <option value="delete">🗑️ Move to Recycle Bin</option>
            <option value="status_available">🔄 Change Status to Available</option>
            <option value="status_lost">🚨 Mark as Lost</option>
        </select>
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apply this action to selected assets?')">Apply</button>
    </div>
</div>
<?php endif; ?>

<div class="table-responsive"><table class="table table-bordered table-striped table-hover">
<thead class="table-dark">
    <tr>
        <?php if (isAdmin()): ?><th width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th><?php endif; ?>
        <th><?= __('code') ?></th><th><?= __('name') ?></th><th><?= __('category') ?></th><th><?= __('location') ?></th><th><?= __('custodian') ?></th><th><?= __('status') ?></th><th><?= __('actions') ?></th>
    </tr>
</thead>
<tbody>
<?php if (empty($rows)): ?><tr><td colspan="<?= isAdmin()?8:7 ?>" class="text-center text-muted py-3">No assets found</td></tr><?php endif; ?>
<?php foreach ($rows as $r): $badge = ['available'=>'success','in_use'=>'primary','under_service'=>'warning','lost'=>'danger','disposed'=>'secondary'][$r['status']] ?? 'secondary'; ?>
<tr>
    <?php if (isAdmin()): ?><td><input type="checkbox" name="ids[]" value="<?= $r['id'] ?>" class="form-check-input row-checkbox"></td><?php endif; ?>
    <td><code><?= e($r['asset_code']) ?></code></td>
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['category'] ?? '-') ?></td>
    <td><?= e($r['location'] ?? '-') ?></td>
    <td><?= e($r['custodian'] ?? '-') ?></td>
    <td><span class="badge bg-<?= $badge ?>"><?= e($r['status']) ?></span></td>
    <td class="table-actions">
        <a class="btn btn-sm btn-primary" href="view.php?id=<?= $r['id'] ?>"><?= __('view') ?></a>
        <?php if (isAdmin()): ?>
        <a class="btn btn-sm btn-warning" href="edit.php?id=<?= $r['id'] ?>"><?= __('edit') ?></a>
        <a class="btn btn-sm btn-danger" href="delete.php?id=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" data-confirm="Move to recycle bin?"><?= __('delete') ?></a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php if (isAdmin()): ?></form><?php endif; ?>

<?php if ($pages > 1): ?>
<nav><ul class="pagination justify-content-center">
<?php for ($i = 1; $i <= $pages; $i++): $qs = $_GET; $qs['page'] = $i; ?>
<li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?<?= http_build_query($qs) ?>"><?= $i ?></a></li>
<?php endfor; ?>
</ul></nav>
<?php endif; ?>

<?php if (isAdmin()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll'); const checkboxes = document.querySelectorAll('.row-checkbox');
    const toolbar = document.getElementById('bulkToolbar'); const countSpan = document.getElementById('selectedCount');
    function updateToolbar() { const checked = document.querySelectorAll('.row-checkbox:checked').length; countSpan.textContent = checked; toolbar.classList.toggle('d-none', checked === 0); }
    if(selectAll) selectAll.addEventListener('change', function() { checkboxes.forEach(cb => cb.checked = selectAll.checked); updateToolbar(); });
    checkboxes.forEach(cb => cb.addEventListener('change', updateToolbar));
});
</script>
<?php endif; ?>
<?php require '../footer.php';