<?php

namespace App\Exceptions;

use Exception;

class CSVImportException extends Exception
{
    protected $rowNumber;
    protected $fieldName;
    protected $invalidValue;

    public function __construct(string $message, int $rowNumber = null, string $fieldName = null, string $invalidValue = null, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->rowNumber = $rowNumber;
        $this->fieldName = $fieldName;
        $this->invalidValue = $invalidValue;
    }

    public function getRowNumber(): ?int
    {
        return $this->rowNumber;
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    public function getInvalidValue(): ?string
    {
        return $this->invalidValue;
    }

    public function getDetailedMessage(): string
    {
        $message = $this->getMessage();
        
        if ($this->rowNumber) {
            $message = "Baris {$this->rowNumber}: {$message}";
        }
        
        if ($this->fieldName && $this->invalidValue) {
            $message .= " (Field: {$this->fieldName}, Value: '{$this->invalidValue}')";
        }
        
        return $message;
    }
}