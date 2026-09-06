<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">Your business account is approved</h1>
<p style="margin:0 0 14px">Good news, <?= $view->e($user->firstName) ?> — <strong><?= $view->e($profile->companyName) ?></strong> is approved.</p>
<p style="margin:0 0 14px">Trade pricing is now active on your account. You will see business rates on every product, and you can request bulk quotes and download GST invoices.</p>
<p style="margin:0 0 14px">Prices drop further as quantities rise — the tier table on each product shows exactly where.</p>
<?php $view->stop(); ?>
