<?php /** @var App\Support\View $view */ $view->extend('layouts/app'); ?>
<?php $view->start('content'); ?>
<div class="wrap" style="max-width:700px">
  <p class="small"><a href="/account">← Account</a></p>
  <h1>Sign-in &amp; security</h1>

  <div class="card" style="margin-top:20px">
    <div class="card-title">Change your password</div>
    <form method="post" action="/account/security/password" novalidate style="margin-top:14px">
      <?= $view->raw($csrf->field()) ?>

      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => [],
          'name' => 'current_password', 'label' => 'Current password', 'type' => 'password',
          'required' => true, 'autocomplete' => 'current-password',
      ]) ?>

      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => [],
          'name' => 'password', 'label' => 'New password', 'type' => 'password',
          'required' => true, 'autocomplete' => 'new-password',
          'hint' => 'At least 10 characters, with a number or symbol.',
      ]) ?>

      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => [],
          'name' => 'password_confirmation', 'label' => 'Confirm new password', 'type' => 'password',
          'required' => true, 'autocomplete' => 'new-password',
      ]) ?>

      <p class="tiny muted">Changing your password signs you out on every other device.</p>
      <button type="submit" class="btn btn-primary">Change password</button>
    </form>
  </div>

  <div class="card">
    <div class="card-title">Other devices</div>
    <p class="small muted" style="margin-top:6px">
      Signed in somewhere you do not recognise? Sign out everywhere else, then change your password.
    </p>
    <form method="post" action="/account/security/sessions">
      <?= $view->raw($csrf->field()) ?>
      <button type="submit" class="btn btn-secondary btn-sm">Sign out of other devices</button>
    </form>
  </div>

  <div class="card">
    <div class="card-title">Recent sign-in activity</div>
    <?php if ($attempts === []): ?>
      <p class="small muted" style="margin-top:8px">No recorded activity yet.</p>
    <?php else: ?>
      <div class="table-wrap" style="margin-top:12px">
        <table>
          <thead><tr><th>When</th><th>Result</th><th>IP address</th></tr></thead>
          <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <tr>
              <td class="tnum"><?= $view->e($attempt['created_at']) ?></td>
              <td>
                <?php if ((int) $attempt['successful'] === 1): ?>
                  <span class="badge badge-approved">Success</span>
                <?php else: ?>
                  <span class="badge badge-rejected">Failed</span>
                <?php endif; ?>
              </td>
              <td class="mono small"><?= $view->e($attempt['ip'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php $view->stop(); ?>
