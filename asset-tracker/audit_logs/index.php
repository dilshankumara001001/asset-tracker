<?php require '../header.php'; requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1)); $perPage = 30; $offset = ($page - 1) * $perPage;
$total = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$pages = max(1, ceil($total / $perPage));

$logs = $pdo->query("SELECT l.*, u.name AS user_name FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.id DESC LIMIT $perPage OFFSET $offset")->fetchAll();
?>
<h2>📝 Audit Log (System History)</h2>
<p class="text-muted">Track who did what and when.</p>

<div class="table-responsive"><table class="table table-sm table-bordered table-striped">
<thead class="table-dark"><tr><th>Date & Time</th><th>User</th><th>IP Address</th><th>Action</th><th>Details</th></tr></thead>
<tbody>
<?php foreach ($logs as $l): 
    $badge = str_contains(strtolower($l['action']), 'fail') ? 'danger' : (str_contains(strtolower($l['action']), 'delete') ? 'warning' : 'secondary');
?>
<tr>
    <td><?= date('Y-m-d H:i:s', strtotime($l['created_at'])) ?></td>
    <td><?= e($l['user_name'] ?? 'System/Guest') ?></td>
    <td><code><?= e($l['ip_address']) ?></code></td>
    <td><span class="badge bg-<?= $badge ?>"><?= e($l['action']) ?></span></td>
    <td><?= e($l['details']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>

<?php if ($pages > 1): ?>
<nav><ul class="pagination justify-content-center">
<?php for ($i = 1; $i <= $pages; $i++): ?>
<li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
<?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php require '../footer.php'; ?>