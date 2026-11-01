<?php

namespace App\Services;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $customMessages;

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
        $this->validate();
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        return new self($data, $rules, $customMessages);
    }

    private function validate(): void
    {
        // Flash old inputs except sensitive fields
        $flashData = $this->data;
        unset($flashData['password'], $flashData['password_confirmation'], $flashData['_csrf_token']);
        $_SESSION['_old_input'] = $flashData;

        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            $label = ucwords(str_replace(['_', '-'], ' ', $field));

            foreach ($rules as $rule) {
                $ruleName = $rule;
                $ruleParam = null;

                if (str_contains($rule, ':')) {
                    [$ruleName, $ruleParam] = explode(':', $rule, 2);
                }

                if ($ruleName === 'required') {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->addError($field, $this->getMessage($field, 'required', "{$label} is required."));
                        break; // Stop further checks on this field if required fails
                    }
                }

                // If field is optional and empty, skip remaining validations
                if ($value === null || $value === '') {
                    continue;
                }

                if ($ruleName === 'email') {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, $this->getMessage($field, 'email', "{$label} must be a valid email address."));
                    }
                } elseif ($ruleName === 'min') {
                    $min = (int)$ruleParam;
                    if (is_string($value) && mb_strlen($value) < $min) {
                        $this->addError($field, $this->getMessage($field, 'min', "{$label} must be at least {$min} characters."));
                    }
                } elseif ($ruleName === 'max') {
                    $max = (int)$ruleParam;
                    if (is_string($value) && mb_strlen($value) > $max) {
                        $this->addError($field, $this->getMessage($field, 'max', "{$label} may not exceed {$max} characters."));
                    }
                } elseif ($ruleName === 'match') {
                    $matchField = $ruleParam;
                    $matchVal = $this->data[$matchField] ?? null;
                    if ($value !== $matchVal) {
                        $matchLabel = ucwords(str_replace(['_', '-'], ' ', $matchField));
                        $this->addError($field, $this->getMessage($field, 'match', "{$label} must match {$matchLabel}."));
                    }
                } elseif ($ruleName === 'in') {
                    $allowed = explode(',', (string)$ruleParam);
                    if (!in_array($value, $allowed, true)) {
                        $this->addError($field, $this->getMessage($field, 'in', "Selected {$label} is invalid."));
                    }
                } elseif ($ruleName === 'alphanumeric') {
                    if (!ctype_alnum(str_replace(['_', '-'], '', (string)$value))) {
                        $this->addError($field, $this->getMessage($field, 'alphanumeric', "{$label} may only contain letters, numbers, dashes, and underscores."));
                    }
                }
            }
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    private function getMessage(string $field, string $rule, string $default): string
    {
        $customKey = "{$field}.{$rule}";
        if (isset($this->customMessages[$customKey])) {
            return $this->customMessages[$customKey];
        }
        if (isset($this->customMessages[$rule])) {
            return $this->customMessages[$rule];
        }
        return $default;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }
}
