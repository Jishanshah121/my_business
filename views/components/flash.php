<?php
/** @var App\Support\View $view */
/** @var array<string,mixed> $flash */
foreach (['success' => 'alert-success', 'error' => 'alert-error', 'warning' => 'alert-warning', 'info' => 'alert-info'] as $key => $class):
    $message = $flash[$key] ?? null;
    if ($message === null || $message === '') { continue; }
?>
<div class="alert <?= $class ?>" role="<?= $key === 'error' ? 'alert' : 'status' ?>">
  <span><?= $view->e($message) ?></span>
</div>
<?php endforeach; ?>
