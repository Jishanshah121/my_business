<?php $view->extend('emails/layout'); $view->start('content'); ?>
<h1 style="font-size:20px;margin:0 0 14px">About your business account</h1>
<p style="margin:0 0 14px">Hello <?= $view->e($user->firstName) ?> — we were not able to approve the business account for <strong><?= $view->e($profile->companyName) ?></strong>.</p>
<p style="margin:0 0 14px;padding:12px 16px;background:#F8ECE2;border-left:3px solid #A9511F;border-radius:0 6px 6px 0"><?= $view->e($reason) ?></p>
<p style="margin:0 0 14px">Your personal account still works normally and you can order at standard prices. Reply to this email or write to sales@supplykaro.test once the details above are sorted and we will take another look.</p>
<?php $view->stop(); ?>
