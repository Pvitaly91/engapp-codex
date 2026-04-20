<?php

namespace App\Exceptions;

use InvalidArgumentException;

class PolyglotLessonValidationException extends InvalidArgumentException
{
    public function __construct(
        protected array $errors,
        string $message = ''
    ) {
        parent::__construct($message !== '' ? $message : $this->formatErrors($errors));
    }

    public static function fromErrors(array $errors): self
    {
        return new self($errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function formatErrors(array $errors): string
    {
        if ($errors === []) {
            return 'Polyglot lesson JSON validation failed.';
        }

        $lines = ['Polyglot lesson JSON validation failed:'];

        foreach ($errors as $error) {
            $uuid = trim((string) ($error['uuid'] ?? ''));
            $field = trim((string) ($error['field'] ?? ''));
            $message = trim((string) ($error['message'] ?? 'Invalid value.'));

            $prefix = $uuid !== '' ? "[{$uuid}]" : '[root]';
            $path = $field !== '' ? " {$field}" : '';

            $lines[] = sprintf('- %s%s: %s', $prefix, $path, $message);
        }

        return implode("\n", $lines);
    }
}
