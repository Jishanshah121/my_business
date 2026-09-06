<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">Your password was changed</h1>
<p style="margin:0 0 14px">Hello <?= $view->e($user->firstName) ?> — the password on your SupplyKaro account has just been changed, and you have been signed out on every device.</p>
<p style="margin:0 0 14px"><strong>If this was not you</strong>, reset your password immediately and email support@supplykaro.test so we can secure the account.</p>
<?php $view->stop(); ?>
