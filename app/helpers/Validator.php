<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class Validator
 * Server-side input validation engine with customizable validation rules and friendly error messages.
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }

            foreach ($fieldRules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                // Check required first
                if ($ruleName === 'required') {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->addError($field, "The {$this->formatFieldName($field)} field is required.");
                        break; // Stop evaluating further rules for this field if empty
                    }
                    continue;
                }

                // If optional and empty, skip remaining rules
                if ($value === null || $value === '') {
                    continue;
                }

                switch ($ruleName) {
                    case 'min':
                        $min = (int) ($params[0] ?? 0);
                        if (is_string($value) && mb_strlen($value) < $min) {
                            $this->addError($field, "The {$this->formatFieldName($field)} must be at least {$min} characters.");
                        } elseif (is_numeric($value) && $value < $min) {
                            $this->addError($field, "The {$this->formatFieldName($field)} must be at least {$min}.");
                        }
                        break;

                    case 'max':
                        $max = (int) ($params[0] ?? 0);
                        if (is_string($value) && mb_strlen($value) > $max) {
                            $this->addError($field, "The {$this->formatFieldName($field)} cannot exceed {$max} characters.");
                        } elseif (is_numeric($value) && $value > $max) {
                            $this->addError($field, "The {$this->formatFieldName($field)} cannot exceed {$max}.");
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, "Please provide a valid email address.");
                        }
                        break;

                    case 'in':
                        if (!in_array((string) $value, $params, true)) {
                            $this->addError($field, "The selected {$this->formatFieldName($field)} is invalid.");
                        }
                        break;

                    case 'matches':
                        $matchField = $params[0] ?? '';
                        $matchValue = $this->data[$matchField] ?? null;
                        if ($value !== $matchValue) {
                            $this->addError($field, "The {$this->formatFieldName($field)} confirmation does not match.");
                        }
                        break;

                    case 'date':
                        $d = \DateTime::createFromFormat('Y-m-d', (string) $value);
                        if (!$d || $d->format('Y-m-d') !== (string) $value) {
                            $this->addError($field, "The {$this->formatFieldName($field)} must be a valid date (YYYY-MM-DD).");
                        }
                        break;

                    case 'unique':
                        $table = $params[0] ?? '';
                        $column = $params[1] ?? $field;
                        $ignoreId = $params[2] ?? null;

                        if ($table) {
                            $db = Database::getInstance();
                            $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
                            $bindings = [':val' => $value];

                            if ($ignoreId !== null && $ignoreId !== '') {
                                $sql .= " AND id != :ignore_id";
                                $bindings[':ignore_id'] = $ignoreId;
                            }

                            $stmt = $db->prepare($sql);
                            $stmt->execute($bindings);
                            if ((int) $stmt->fetchColumn() > 0) {
                                $this->addError($field, "This {$this->formatFieldName($field)} is already registered.");
                            }
                        }
                        break;
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function formatFieldName(string $field): string
    {
        return str_replace(['_', '-'], ' ', $field);
    }
}
