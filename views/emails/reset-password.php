<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">Reset your password</h1>
<p style="margin:0 0 18px">Hello <?= $view->e($user->firstName) ?> — someone asked to reset the password on your SupplyKaro account.</p>
<p style="margin:0 0 22px">
  <a href="<?= $view->e($url) ?>" style="display:inline-block;background:#1E4C3A;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600">Choose a new password</a>
</p>
<p style="margin:0 0 10px;color:#6A7770;font-size:13px">This link expires in <?= (int) $expiresMinutes ?> minutes and can be used once.</p>
<p style="margin:0 0 10px;color:#6A7770;font-size:13px"><strong>Did not request this?</strong> You can ignore this email — your password stays as it is.</p>
<p style="margin:0;color:#6A7770;font-size:13px">If the button does not work, paste this into your browser:<br>
<span style="word-break:break-all;font-family:monospace;font-size:12px"><?= $view->e($url) ?></span></p>
<?php $view->stop(); ?>
