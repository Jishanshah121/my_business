<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

/**
 * Rule-based input validation.
 *
 * Every field that reaches the database passes through here. Rules are written
 * as "required|email|max:191" strings; India-specific rules (GSTIN with its
 * checksum, PAN, pincode, mobile) live here rather than being re-implemented
 * per form.
 */
final class Validator
{
    /** @var array<string,list<string>> */
    private array $errors = [];

    /** @var array<string,string> */
    private array $labels = [];

    /** @var array<string,string> */
    private array $customMessages = [];

    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $rules
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $rules
     * @param array<string,string> $labels Human field names for messages.
     */
    public static function make(array $data, array $rules, array $labels = []): self
    {
        $validator = new self($data, $rules);
        $validator->labels = $labels;

        return $validator;
    }

    /** @param array<string,string> $messages field.rule => message */
    public function messages(array $messages): self
    {
        $this->customMessages = $messages;

        return $this;
    }

    /** Register an extra check, e.g. a uniqueness lookup. */
    public function after(string $field, callable $check): self
    {
        $result = $check($this->value($field));
        if (is_string($result)) {
            $this->addError($field, $result);
        }

        return $this;
    }

    public function passes(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->value($field);
            $rules = explode('|', $ruleString);

            // "nullable" means: skip everything else when the field is empty.
            if (in_array('nullable', $rules, true) && $this->isEmpty($value)) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

                // Only "required" and its variants fire on an empty value;
                // otherwise an optional blank field would fail every rule.
                if ($this->isEmpty($value) && !str_starts_with($name, 'required') && $name !== 'accepted') {
                    continue;
                }

                $this->applyRule($field, $name, $value, $parameter);
            }
        }

        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Validate, or throw with the messages and the safe subset of the input.
     *
     * @return array<string,mixed> The validated fields only.
     */
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors, $this->safeOld());
        }

        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->value($field);
            }
        }

        return $validated;
    }

    /** @return array<string,list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Input to repopulate the form with — never anything secret.
     *
     * @return array<string,mixed>
     */
    private function safeOld(): array
    {
        $old = [];
        foreach ($this->data as $key => $value) {
            $normalised = strtolower((string) $key);
            if (str_contains($normalised, 'password') || str_contains($normalised, 'token')) {
                continue;
            }
            if (is_scalar($value)) {
                $old[$key] = $value;
            }
        }

        return $old;
    }

    private function applyRule(string $field, string $name, mixed $value, ?string $parameter): void
    {
        $label = $this->label($field);

        switch ($name) {
            case 'required':
                if ($this->isEmpty($value)) {
                    $this->addError($field, "{$label} is required.", 'required');
                }
                break;

            case 'required_if':
                [$otherField, $otherValue] = array_pad(explode(',', (string) $parameter, 2), 2, '1');
                if ((string) $this->value($otherField) === $otherValue && $this->isEmpty($value)) {
                    $this->addError($field, "{$label} is required.", 'required_if');
                }
                break;

            case 'accepted':
                if (!in_array($value, [true, 1, '1', 'on', 'yes', 'true'], true)) {
                    $this->addError($field, "Please accept the {$label}.", 'accepted');
                }
                break;

            case 'email':
                if (filter_var((string) $value, FILTER_VALIDATE_EMAIL) === false) {
                    $this->addError($field, 'Enter a valid email address.', 'email');
                }
                break;

            case 'min':
                if (mb_strlen((string) $value) < (int) $parameter) {
                    $this->addError($field, "{$label} must be at least {$parameter} characters.", 'min');
                }
                break;

            case 'max':
                if (mb_strlen((string) $value) > (int) $parameter) {
                    $this->addError($field, "{$label} must be {$parameter} characters or fewer.", 'max');
                }
                break;

            case 'between':
                [$low, $high] = array_pad(explode(',', (string) $parameter, 2), 2, '0');
                $number = (float) $value;
                if ($number < (float) $low || $number > (float) $high) {
                    $this->addError($field, "{$label} must be between {$low} and {$high}.", 'between');
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, "{$label} must be a number.", 'numeric');
                }
                break;

            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "{$label} must be a whole number.", 'integer');
                }
                break;

            case 'boolean':
                if (!in_array($value, [true, false, 0, 1, '0', '1', 'on', ''], true)) {
                    $this->addError($field, "{$label} must be yes or no.", 'boolean');
                }
                break;

            case 'in':
                $allowed = explode(',', (string) $parameter);
                if (!in_array((string) $value, $allowed, true)) {
                    $this->addError($field, "Choose a valid {$label}.", 'in');
                }
                break;

            case 'confirmed':
                if ((string) $value !== (string) $this->value($field . '_confirmation')) {
                    $this->addError($field, "{$label} does not match the confirmation.", 'confirmed');
                }
                break;

            case 'same':
                if ((string) $value !== (string) $this->value((string) $parameter)) {
                    $this->addError($field, "{$label} must match {$this->label((string) $parameter)}.", 'same');
                }
                break;

            case 'different':
                if ((string) $value === (string) $this->value((string) $parameter)) {
                    $this->addError($field, "{$label} must be different from {$this->label((string) $parameter)}.", 'different');
                }
                break;

            case 'regex':
                if (preg_match((string) $parameter, (string) $value) !== 1) {
                    $this->addError($field, "{$label} is not in the expected format.", 'regex');
                }
                break;

            case 'alpha_num':
                if (preg_match('/^[a-zA-Z0-9]+$/', (string) $value) !== 1) {
                    $this->addError($field, "{$label} may contain only letters and numbers.", 'alpha_num');
                }
                break;

            case 'name':
                // Letters, spaces, apostrophes, hyphens and dots — enough for
                // Indian and international names without opening up injection.
                if (preg_match("/^[\p{L}\p{M}][\p{L}\p{M}\s'.\-]{0,95}$/u", (string) $value) !== 1) {
                    $this->addError($field, "Enter a valid {$label}.", 'name');
                }
                break;

            case 'password':
                if (!$this->isStrongPassword((string) $value)) {
                    $this->addError(
                        $field,
                        'Use at least 10 characters with a mix of letters and numbers.',
                        'password'
                    );
                }
                break;

            case 'phone':
                if (!self::isValidIndianMobile((string) $value)) {
                    $this->addError($field, 'Enter a valid 10-digit Indian mobile number.', 'phone');
                }
                break;

            case 'pincode':
                if (preg_match('/^[1-9][0-9]{5}$/', (string) $value) !== 1) {
                    $this->addError($field, 'Enter a valid 6-digit pincode.', 'pincode');
                }
                break;

            case 'gstin':
                if (!self::isValidGstin((string) $value)) {
                    $this->addError($field, 'That GSTIN is not valid. Check all 15 characters.', 'gstin');
                }
                break;

            case 'pan':
                if (preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', strtoupper((string) $value)) !== 1) {
                    $this->addError($field, 'Enter a valid PAN, for example ABCDE1234F.', 'pan');
                }
                break;

            case 'state_code':
                $codes = array_keys((array) \App\Support\Config::get('tax.state_codes', []));
                if (!in_array((string) $value, $codes, true)) {
                    $this->addError($field, 'Choose a valid state.', 'state_code');
                }
                break;

            case 'date':
                if (strtotime((string) $value) === false) {
                    $this->addError($field, "{$label} must be a valid date.", 'date');
                }
                break;

            default:
                // An unknown rule is a programming error, not user input.
                throw new \RuntimeException("Unknown validation rule [{$name}] on field [{$field}].");
        }
    }

    /**
     * Length first, then variety. Deliberately not a maze of character-class
     * requirements: length does far more for entropy, and complex rules push
     * people towards Password1! patterns.
     */
    private function isStrongPassword(string $password): bool
    {
        $minimum = (int) \App\Support\Config::get('security.password.min_length', 10);

        if (mb_strlen($password) < $minimum) {
            return false;
        }

        $hasLetter = preg_match('/\p{L}/u', $password) === 1;
        $hasNumberOrSymbol = preg_match('/[\d\W_]/u', $password) === 1;

        return $hasLetter && $hasNumberOrSymbol;
    }

    /** 10 digits starting 6-9, with or without a 91 / +91 prefix. */
    public static function isValidIndianMobile(string $phone): bool
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        return preg_match('/^[6-9]\d{9}$/', $digits) === 1;
    }

    /** Normalise to the storage format: 91XXXXXXXXXX. */
    public static function normaliseIndianMobile(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return '91' . $digits;
    }

    /**
     * GSTIN format plus the mod-36 check digit.
     *
     * Format alone accepts typos; the checksum is what actually catches a
     * mistyped GSTIN before it ends up on an invoice.
     */
    public static function isValidGstin(string $gstin): bool
    {
        $gstin = strtoupper(trim($gstin));

        if (preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gstin) !== 1) {
            return false;
        }

        // First two characters must be a real state code.
        $stateCodes = (array) \App\Support\Config::get('tax.state_codes', []);
        if ($stateCodes !== [] && !array_key_exists(substr($gstin, 0, 2), $stateCodes)) {
            return false;
        }

        $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $factor = 2;
        $sum = 0;

        for ($i = 13; $i >= 0; $i--) {
            $codePoint = strpos($charset, $gstin[$i]);
            if ($codePoint === false) {
                return false;
            }

            $digit = $factor * $codePoint;
            $factor = $factor === 2 ? 1 : 2;
            $sum += intdiv($digit, 36) + $digit % 36;
        }

        $expected = $charset[(36 - $sum % 36) % 36];

        return $expected === $gstin[14];
    }

    /** The state code embedded in a GSTIN — the buyer's place of supply. */
    public static function stateCodeFromGstin(string $gstin): ?string
    {
        return self::isValidGstin($gstin) ? substr(strtoupper(trim($gstin)), 0, 2) : null;
    }

    private function value(string $field): mixed
    {
        $value = $this->data[$field] ?? null;

        return is_string($value) ? trim($value) : $value;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function label(string $field): string
    {
        return $this->labels[$field] ?? ucfirst(str_replace(['_', '.'], ' ', $field));
    }

    private function addError(string $field, string $message, ?string $rule = null): void
    {
        if ($rule !== null && isset($this->customMessages["{$field}.{$rule}"])) {
            $message = $this->customMessages["{$field}.{$rule}"];
        }

        $this->errors[$field][] = $message;
    }
}
