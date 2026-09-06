<?php /** @var App\Support\View $view */ ?><!doctype html>
<html lang="en-IN">
<head><meta charset="utf-8"><title><?= $view->e($subject ?? 'SupplyKaro') ?></title></head>
<body style="margin:0;padding:0;background:#F6F7F4;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#141A16">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F6F7F4;padding:28px 12px">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFFFF;border:1px solid #D6DED4;border-radius:10px">
        <tr><td style="padding:22px 28px;border-bottom:1px solid #D6DED4">
          <span style="font-size:18px;font-weight:700;letter-spacing:-.02em">SupplyKaro</span>
          <span style="color:#6A7770;font-size:13px"> — serve, pack &amp; celebrate</span>
        </td></tr>
        <tr><td style="padding:28px;font-size:15px;line-height:1.65"><?= $view->section('content') ?></td></tr>
        <tr><td style="padding:18px 28px;border-top:1px solid #D6DED4;color:#6A7770;font-size:12px;line-height:1.5">
          You received this because you have a SupplyKaro account.<br>
          This is a development build — placeholder content.
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
