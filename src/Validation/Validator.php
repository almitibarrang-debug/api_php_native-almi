<?php

namespace Src\Validation;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    private function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;

            foreach (explode('|', $ruleString) as $rule) {
                if ($rule === 'required' && ($value === null || $value === '')) {
                    $this->errors[$field][] = 'required';
                } elseif (str_starts_with($rule, 'min:')) {
                    $minLength = (int)substr($rule, 4);
                    if (strlen((string)$value) < $minLength) {
                        $this->errors[$field][] = $rule;
                    }
                } elseif (str_starts_with($rule, 'max:')) {
                    $maxLength = (int)substr($rule, 4);
                    if (strlen((string)$value) > $maxLength) {
                        $this->errors[$field][] = $rule;
                    }
                } elseif ($rule === 'email' && $value !== null) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = 'email';
                    }
                } elseif ($rule === 'numeric' && $value !== null) {
                    if (!is_numeric($value)) {
                        $this->errors[$field][] = 'numeric';
                    }
                } elseif ($rule === 'integer' && $value !== null) {
                    if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                        $this->errors[$field][] = 'integer';
                    }
                } elseif (str_starts_with($rule, 'enum:')) {
                    $options = explode(',', substr($rule, 5));
                    if ($value !== null && !in_array($value, $options, true)) {
                        $this->errors[$field][] = 'enum';
                    }
                }
            }
        }

        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public static function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }
        return $data;
    }
}
