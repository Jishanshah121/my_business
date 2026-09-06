<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">New business account to review</h1>
<p style="margin:0 0 14px"><strong><?= $view->e($profile->companyName) ?></strong> has registered and is waiting for approval.</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.7">
  <tr><td style="color:#6A7770;padding-right:16px">Business type</td><td><?= $view->e($profile->businessTypeName ?? '—') ?></td></tr>
  <tr><td style="color:#6A7770;padding-right:16px">Contact</td><td><?= $view->e($profile->contactPerson) ?> · <?= $view->e($profile->contactPhone) ?></td></tr>
  <tr><td style="color:#6A7770;padding-right:16px">GSTIN</td><td style="font-family:monospace"><?= $view->e($profile->gstin ?? '—') ?></td></tr>
  <tr><td style="color:#6A7770;padding-right:16px">Account email</td><td><?= $view->e($user->email) ?></td></tr>
</table>
<p style="margin:18px 0 0;color:#6A7770;font-size:13px">Approve or reject from Admin → Business accounts.</p>
<?php $view->stop(); ?>
