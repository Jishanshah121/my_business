<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">Welcome, <?= $view->e($user->firstName) ?>.</h1>
<p style="margin:0 0 14px">Your SupplyKaro account is ready. You can now order disposables, packaging, tissues and event supplies in any quantity — from a single pack to a full carton.</p>
<p style="margin:0 0 14px">We have sent a separate email to confirm your address. Confirming it lets us send order updates and GST invoices.</p>
<p style="margin:0 0 6px"><strong>Buying for a business?</strong></p>
<p style="margin:0 0 14px">Register your GSTIN to unlock trade pricing, bulk quotes and repeat ordering.</p>
<?php $view->stop(); ?>
