<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">Confirm your email address</h1>
<p style="margin:0 0 18px">Hello <?= $view->e($user->firstName) ?> — please confirm this address so we can send you order updates and invoices.</p>
<p style="margin:0 0 22px">
  <a href="<?= $view->e($url) ?>" style="display:inline-block;background:#1E4C3A;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600">Confirm my email</a>
</p>
<p style="margin:0 0 10px;color:#6A7770;font-size:13px">This link expires in <?= (int) $expiresHours ?> hours and can be used once.</p>
<p style="margin:0;color:#6A7770;font-size:13px">If the button does not work, paste this into your browser:<br>
<span style="word-break:break-all;font-family:monospace;font-size:12px"><?= $view->e($url) ?></span></p>
<?php $view->stop(); ?>
