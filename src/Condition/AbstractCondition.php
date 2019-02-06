<?php

namespace Parable\Query\Condition;

use Parable\Query\Exception;

abstract class AbstractCondition
{
    public const TYPE_AND = 'AND';
    public const TYPE_OR = 'OR';

    public const VALUE_TYPE_VALUE = 1;
    public const VALUE_TYPE_KEY = 2;

    /**
     * @var string
     */
    protected $type = self::TYPE_AND;

    /**
     * @var bool
     */
    protected $valueIsKey = false;

    public function setType(string $type): void
    {
        $type = strtoupper($type);

        if (!in_array($type, [self::TYPE_AND, self::TYPE_OR])) {
            throw new Exception('Invalid where type provided: ' . $type);
        }

        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setValueIsKey(bool $valueIsKey): void
    {
        $this->valueIsKey = $valueIsKey;
    }

    public function isValueKey(): bool
    {
        return $this->valueIsKey;
    }
}
