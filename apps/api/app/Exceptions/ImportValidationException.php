<?php

namespace App\Exceptions;

use RuntimeException;

class ImportValidationException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly array $unrecognizedColumns = [],
        private readonly ?string $suggestion = null,
    ) {
        parent::__construct($message);
    }

    public function unrecognizedColumns(): array
    {
        return $this->unrecognizedColumns;
    }

    public function suggestion(): ?string
    {
        return $this->suggestion;
    }
}
