<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class Validator
{
    /** @param array<string, mixed> $data @param array<string, string> $rules @return array<string, string> */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleLine) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleLine) as $rule) {
                if ($rule === 'required' && trim((string) $value) === '') {
                    $errors[$field] = 'required';
                }
                if ($rule === 'email' && $value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'email';
                }
                if (str_starts_with($rule, 'min:') && mb_strlen((string) $value) < (int) substr($rule, 4)) {
                    $errors[$field] = 'min';
                }
                if ($rule === 'accepted' && !in_array($value, ['1', 1, true, 'on'], true)) {
                    $errors[$field] = 'accepted';
                }
            }
        }
        return $errors;
    }
}
