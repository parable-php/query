<?php

namespace Parable\Query;

use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;

class Join
{
    /**
     * @var string|null
     */
    protected $tableName;

    /**
     * @var string|null
     */
    protected $tableAlias;

    /**
     * @var ValueCondition[]|CallableCondition[]
     */
    protected $onConditions = [];

    public function __construct(string $tableName, ?string $tableAlias = null)
    {
        $this->tableName = $tableName;
        $this->tableAlias = $tableAlias;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getTableAlias(): ?string
    {
        return $this->tableAlias;
    }

    public function getTableAliasOrName(): string
    {
        if ($this->tableAlias !== null) {
            return $this->tableAlias;
        }

        return $this->getTableName();
    }

    public function on(string $joinKey, string $comparator, string $value): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(), $joinKey, $comparator, $value, 'AND', false
        );

        return $this;
    }

    public function orOn(string $joinKey, string $comparator, string $value): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(), $joinKey, $comparator, $value, 'OR', false
        );

        return $this;
    }

    public function onNull(string $key): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(), $key, 'IS NULL', null, 'AND', false
        );

        return $this;
    }

    public function onNotNull(string $key): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(), $key, 'IS NOT NULL', null, 'AND', false
        );

        return $this;
    }

    public function onKey(string $key, string $comparator, string $queryKey): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(), $key, $comparator, $queryKey, 'AND', true
        );

        return $this;
    }

    public function orOnKey(string $key, string $comparator, string $queryKey): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(), $key, $comparator, $queryKey, 'OR', true
        );

        return $this;
    }

    public function whereCallable(callable $callable): self
    {
        $this->onConditions[] = new CallableCondition($callable, 'AND');

        return $this;
    }

    public function orWhereCallable(callable $callable): self
    {
        $this->onConditions[] = new CallableCondition($callable, 'OR');

        return $this;
    }

    /**
     * @return ValueCondition[]|CallableCondition[]
     */
    public function getOnConditions(): array
    {
        return $this->onConditions;
    }

    protected function createValueCondition(
        string $tableName,
        string $key,
        string $comparator,
        $values = null,
        ?string $type = null,
        bool $valueIsKey = false
    ): ValueCondition {
        return new ValueCondition(
            $tableName,
            $key,
            $comparator,
            $values,
            $type,
            $valueIsKey
        );
    }
}
