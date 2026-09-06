<?php
/**
 * One form field: label, control, hint and error, wired up for screen readers.
 *
 * @var App\Support\View $view
 * @var array<string,list<string>> $errors
 * @var array<string,mixed> $old
 */
$name        = $name        ?? '';
$label       = $label       ?? ucfirst(str_replace('_', ' ', $name));
$type        = $type        ?? 'text';
$required    = $required    ?? false;
$hint        = $hint        ?? null;
$value       = $value       ?? ($old[$name] ?? '');
$autocomplete = $autocomplete ?? null;
$placeholder = $placeholder ?? null;
$options     = $options     ?? null;   // select
$attrs       = $attrs       ?? '';
$fieldErrors = $errors[$name] ?? [];
$hasError    = $fieldErrors !== [];
$describedBy = [];
if ($hint !== null)  { $describedBy[] = $name . '-hint'; }
if ($hasError)       { $describedBy[] = $name . '-error'; }
?>
<div class="field <?= $hasError ? 'has-error' : '' ?>">
  <label for="<?= $view->e($name) ?>">
    <?= $view->e($label) ?><?php if ($required): ?> <span class="req" aria-hidden="true">*</span><?php endif; ?>
  </label>

  <?php if ($options !== null): ?>
    <select id="<?= $view->e($name) ?>" name="<?= $view->e($name) ?>"
            <?= $required ? 'required' : '' ?>
            <?= $hasError ? 'aria-invalid="true"' : '' ?>
            <?= $describedBy !== [] ? 'aria-describedby="' . $view->e(implode(' ', $describedBy)) . '"' : '' ?>>
      <option value=""><?= $view->e($placeholder ?? 'Choose one') ?></option>
      <?php foreach ($options as $optValue => $optLabel): ?>
        <option value="<?= $view->e($optValue) ?>" <?= (string) $value === (string) $optValue ? 'selected' : '' ?>>
          <?= $view->e($optLabel) ?>
        </option>
      <?php endforeach; ?>
    </select>
  <?php else: ?>
    <div class="field-control-wrap <?= $type === 'password' ? 'password-wrap' : '' ?>">
      <input type="<?= $view->e($type) ?>" id="<?= $view->e($name) ?>" name="<?= $view->e($name) ?>"
             value="<?= $type === 'password' ? '' : $view->e($value) ?>"
             <?= $required ? 'required' : '' ?>
             <?= $autocomplete !== null ? 'autocomplete="' . $view->e($autocomplete) . '"' : '' ?>
             <?= $placeholder !== null ? 'placeholder="' . $view->e($placeholder) . '"' : '' ?>
             <?= $hasError ? 'aria-invalid="true"' : '' ?>
             <?= $describedBy !== [] ? 'aria-describedby="' . $view->e(implode(' ', $describedBy)) . '"' : '' ?>
             <?= $view->raw($attrs) ?>>
      <?php if ($type === 'password'): ?>
        <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" data-target="<?= $view->e($name) ?>">
          <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg class="eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($hint !== null): ?>
    <div class="hint" id="<?= $view->e($name) ?>-hint"><?= $view->e($hint) ?></div>
  <?php endif; ?>

  <?php if ($hasError): ?>
    <div class="field-error" id="<?= $view->e($name) ?>-error"><?= $view->e($fieldErrors[0]) ?></div>
  <?php endif; ?>
</div>
