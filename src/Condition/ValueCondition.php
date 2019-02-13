<?php declare(strict_types=1);

namespace Parable\Query\Condition;

class ValueCondition extends AbstractCondition
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $comparator;

    /**
     * @var mixed
     */
    protected $value;

    public function __construct(
        string $tableName,
        string $key,
        string $comparator,
        $value = null,
        ?string $type = null,
        bool $valueIsKey = false
    ) {
        $this->tableName = $tableName;
        $this->key = $key;
        $this->comparator = $comparator;
        $this->value = $value;

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
