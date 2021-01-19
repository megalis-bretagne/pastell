<?php

namespace Pastell\System;

class HealthCheckItem
{
    private const ERROR = 'error';
    private const INFO = 'info';
    private const SUCCESS = 'success';

    /** @var string */
    public $label;
    /** @var string */
    public $result;
    /** @var string */
    private $level;
    /** @var string|null */
    public $expectedValue;
    /** @var HealthCheckItem[]|null */
    private $details;

    public function __construct(string $label, string $result, ?string $expectedValue = null)
    {
        $this->label = $label;
        $this->result = $result;
        $this->level = self::INFO;
        if (isset($expectedValue)) {
            $this->expectedValue = $expectedValue;
        }
    }

    public function isInfo(): bool
    {
        return $this->level === self::INFO;
    }

    public function isSuccess(): bool
    {
        return $this->level === self::SUCCESS;
    }

    public function isError(): bool
    {
        return $this->level === self::ERROR;
    }

    public function setInfo(): void
    {
        $this->level = self::INFO;
    }

    public function setSuccess(bool $isSuccess): self
    {
        $this->level = $isSuccess ? self::SUCCESS : self::ERROR;
        return $this;
    }

    /**
     * @return HealthCheckItem[]|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * @param HealthCheckItem[]|null $details
     */
    public function setDetails(?array $details): self
    {
        $this->details = $details;
        return $this;
    }
}
