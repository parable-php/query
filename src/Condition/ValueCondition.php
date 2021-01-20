<?php declare(strict_types=1);

namespace Parable\Query\Condition;

class ValueCondition extends AbstractCondition
{
    public function __construct(
        protected string $tableName,
        protected string $key,
        protected string $comparator,
        protected $value = null,
        ?string $type = null,
        bool $valueIsKey = false
    ) {
        if ($type !== null) {
            $this->setType($type);
        }

        $this->setValueIsKey($valueIsKey);
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getComparator(): string
    {
        return $this->comparator;
    }

    public function getValue()
    {
        return $this->value;
    }
}
