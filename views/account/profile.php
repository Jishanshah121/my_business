<?php /** @var App\Support\View $view */ $view->extend('layouts/app'); ?>
<?php $view->start('content'); ?>
<div class="wrap" style="max-width:640px">
  <p class="small"><a href="/account">← Account</a></p>
  <h1>Your details</h1>

  <div class="card" style="margin-top:20px">
    <form method="post" action="/account/profile" novalidate>
      <?= $view->raw($csrf->field()) ?>

      <div class="row row-2">
        <?= $view->include('components/field', [
            'view' => $view, 'errors' => $errors, 'old' => $old,
            'name' => 'first_name', 'label' => 'First name', 'required' => true,
            'value' => $old['first_name'] ?? $user->firstName,
        ]) ?>
        <?= $view->include('components/field', [
            'view' => $view, 'errors' => $errors, 'old' => $old,
            'name' => 'last_name', 'label' => 'Last name',
            'value' => $old['last_name'] ?? ($user->lastName ?? ''),
        ]) ?>
      </div>

      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => $old,
          'name' => 'phone', 'label' => 'Mobile number', 'type' => 'tel',
          'value' => $old['phone'] ?? ($user->phone ?? ''),
          'hint' => 'Used for delivery updates.',
      ]) ?>

      <div class="field">
        <label>Email address</label>
        <input type="email" value="<?= $view->e($user->email) ?>" disabled>
        <div class="hint">
          <?php if ($user->hasVerifiedEmail()): ?>
            Confirmed. Contact support to change your email address.
          <?php else: ?>
            Not confirmed yet — <a href="/email/verify">send a new link</a>.
          <?php endif; ?>
        </div>
      </div>

      <div class="field">
        <label class="checkbox">
          <input type="checkbox" name="marketing_opt_in" value="1" <?= $user->marketingOptIn ? 'checked' : '' ?>>
          <span>Send me occasional offers and new product news.</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
  </div>
</div>
<?php $view->stop(); ?>
