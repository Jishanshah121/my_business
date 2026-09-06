<?php /** @var App\Support\View $view */ $view->extend('layouts/admin', ['title' => 'B2B Trade Account Approvals']); ?>
<?php $view->start('content'); ?>
<div>

  <nav class="site-nav" style="margin:18px 0 0;justify-content:flex-start">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
      <a href="/admin/business-accounts?status=<?= $view->e($key) ?>" class="<?= $status === $key ? 'active' : '' ?>">
        <?= $view->e($label) ?> (<?= (int) ($counts[$key] ?? 0) ?>)
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if ($profiles === []): ?>
    <div class="card" style="margin-top:18px">
      <p class="muted" style="margin:0">Nothing <?= $view->e($status) ?> right now.</p>
    </div>
  <?php else: ?>
    <?php foreach ($profiles as $p): ?>
      <div class="card" style="margin-top:16px">
        <div class="card-header">
          <div>
            <div class="card-title"><?= $view->e($p['company_name']) ?></div>
            <div class="small muted">
              <?= $view->e($p['business_type_name'] ?? 'Business') ?> ·
              <?= $view->e(trim($p['first_name'] . ' ' . ($p['last_name'] ?? ''))) ?> ·
              <?= $view->e($p['email']) ?>
            </div>
          </div>
          <span class="badge badge-<?= $view->e($p['status'] === 'approved' ? 'approved' : ($p['status'] === 'rejected' ? 'rejected' : 'pending')) ?>">
            <?= $view->e($p['status']) ?>
          </span>
        </div>

        <div class="table-wrap">
          <table>
            <tbody>
              <tr>
                <th style="width:180px">GSTIN</th>
                <td class="mono"><?= $view->e($p['gstin'] ?? '—') ?></td>
                <th style="width:180px">PAN</th>
                <td class="mono"><?= $view->e($p['pan'] ?? '—') ?></td>
              </tr>
              <tr>
                <th>Contact</th>
                <td><?= $view->e($p['contact_person']) ?><br><span class="mono small"><?= $view->e($p['contact_phone']) ?></span></td>
                <th>Expected monthly</th>
                <td class="tnum"><?= $p['expected_monthly_spend'] !== null ? '₹' . number_format((float) $p['expected_monthly_spend'], 0) : '—' ?></td>
              </tr>
              <tr>
                <th>Registered</th>
                <td class="tnum" colspan="3"><?= $view->e($p['created_at']) ?></td>
              </tr>
            </tbody>
          </table>
        </div>

        <?php if ($p['status'] === 'pending' && $gate->allows('b2b.approve')): ?>
          <div style="display:grid;gap:14px;margin-top:16px">
            <form method="post" action="/admin/business-accounts/<?= (int) $p['id'] ?>/approve" class="actions">
              <?= $view->raw($csrf->field()) ?>
              <label class="small muted" for="grp-<?= (int) $p['id'] ?>">Pricing group</label>
              <select name="customer_group" id="grp-<?= (int) $p['id'] ?>" style="width:auto">
                <option value="b2b_standard">Business Standard</option>
                <option value="b2b_gold">Business Gold</option>
                <option value="distributor">Distributor</option>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Approve</button>
            </form>

            <form method="post" action="/admin/business-accounts/<?= (int) $p['id'] ?>/reject" class="actions">
              <?= $view->raw($csrf->field()) ?>
              <input type="text" name="reason" placeholder="Reason — the customer sees this" style="flex:1;min-width:240px" required minlength="10" maxlength="500">
              <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
            </form>
          </div>
        <?php elseif ($p['status'] === 'rejected' && !empty($p['rejection_reason'])): ?>
          <p class="small muted" style="margin:12px 0 0"><strong>Reason given:</strong> <?= $view->e($p['rejection_reason']) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php $view->stop(); ?>
