<?php /** @var App\Support\View $view */ $view->extend('layouts/app'); ?>
<?php $view->start('content'); ?>
<div class="wrap" style="max-width:700px">
  <p class="small"><a href="/account">← Account</a></p>
  <h1>Business account</h1>

  <?php if ($profile === null): ?>
    <div class="card" style="margin-top:20px">
      <p>You do not have a business account yet.</p>
      <p class="small muted">
        A business account gets you trade pricing on bulk quantities, GST invoices,
        bulk quote requests and one-click repeat ordering.
      </p>
      <a class="btn btn-primary" href="/register/business">Register your business</a>
    </div>
  <?php else: ?>
    <div class="card" style="margin-top:20px">
      <div class="card-header">
        <div>
          <div class="card-title"><?= $view->e($profile->companyName) ?></div>
          <div class="small muted"><?= $view->e($profile->businessTypeName ?? 'Business') ?></div>
        </div>
        <span class="badge badge-<?= $view->e($profile->isApproved() ? 'approved' : ($profile->isRejected() ? 'rejected' : 'pending')) ?>">
          <?= $view->e($profile->statusLabel()) ?>
        </span>
      </div>

      <?php if ($profile->isPending()): ?>
        <div class="alert alert-warning">
          <span>We are reviewing your details, usually within one working day. You can order at standard prices in the meantime.</span>
        </div>
      <?php elseif ($profile->isRejected()): ?>
        <div class="alert alert-error">
          <span>
            <strong>Not approved.</strong>
            <?= $view->e($profile->rejectionReason ?? 'Contact sales@supplykaro.test to sort this out.') ?>
          </span>
        </div>
      <?php elseif ($profile->isApproved()): ?>
        <div class="alert alert-success">
          <span>Approved. Trade pricing is active on your account.</span>
        </div>
      <?php endif; ?>

      <div class="table-wrap" style="margin-top:8px">
        <table>
          <tbody>
            <tr><th style="width:200px">Contact person</th><td><?= $view->e($profile->contactPerson) ?></td></tr>
            <tr><th>Contact number</th><td class="mono"><?= $view->e($profile->contactPhone) ?></td></tr>
            <tr><th>GSTIN</th><td class="mono"><?= $view->e($profile->gstin ?? '—') ?></td></tr>
            <tr><th>PAN</th><td class="mono"><?= $view->e($profile->pan ?? '—') ?></td></tr>
            <?php if ($profile->expectedMonthlySpend !== null): ?>
              <tr><th>Expected monthly purchase</th><td class="tnum">₹<?= $view->e(number_format((float) $profile->expectedMonthlySpend, 2)) ?></td></tr>
            <?php endif; ?>
            <?php if ($profile->creditEnabled): ?>
              <tr><th>Credit limit</th><td class="tnum">₹<?= $view->e(number_format((float) $profile->creditLimit, 2)) ?> · net <?= (int) $profile->creditDays ?> days</td></tr>
              <tr><th>Credit available</th><td class="tnum">₹<?= $view->e(number_format((float) $profile->creditAvailable(), 2)) ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Coming next</div>
      <p class="small muted" style="margin-top:8px">
        Your business dashboard — monthly spend, savings against list price, frequently ordered
        products, open quotes and reorder prompts — is built in Phase 5, once ordering exists.
      </p>
    </div>
  <?php endif; ?>
</div>
<?php $view->stop(); ?>
