<?php declare(strict_types=1);

namespace Parable\Query;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;

class Join
{
    /** @var ValueCondition[]|CallableCondition[] */
    protected array $onConditions = [];

    public function __construct(
        protected string $tableName,
        protected ?string $tableAlias = null
    ) {}

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
        return $this->tableAlias ?? $this->getTableName();
    }

    public function on(string $joinKey, string $comparator, string $value): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(),
            $joinKey,
            $comparator,
            $value,
            AbstractCondition::TYPE_AND
        );

        return $this;
    }

    public function orOn(string $joinKey, string $comparator, string $value): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(),
            $joinKey,
            $comparator,
            $value,
            AbstractCondition::TYPE_OR
        );

        return $this;
    }

    public function onNull(string $key): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(),
            $key,
            'IS NULL',
            null,
            AbstractCondition::TYPE_AND
        );

        return $this;
    }

    public function onNotNull(string $key): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(),
            $key,
            'IS NOT NULL',
            null,
            AbstractCondition::TYPE_AND
        );

        return $this;
    }

    public function onKey(string $key, string $comparator, string $queryKey): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(),
            $key,
            $comparator,
            $queryKey,
            AbstractCondition::TYPE_AND,
            true
        );

        return $this;
    }

    public function orOnKey(string $key, string $comparator, string $queryKey): self
    {
        $this->onConditions[] = $this->createValueCondition(
            $this->getTableAliasOrName(),
            $key,
            $comparator,
            $queryKey,
            AbstractCondition::TYPE_OR,
            true
        );

        return $this;
    }

    public function onCallable(callable $callable): self
    {
        $this->onConditions[] = new CallableCondition($callable, AbstractCondition::TYPE_AND);

        return $this;
    }

    public function orOnCallable(callable $callable): self
    {
        $this->onConditions[] = new CallableCondition($callable, AbstractCondition::TYPE_OR);

        return $this;
    }

    public function onCondition(AbstractCondition $condition): self
    {
        $this->onConditions[] = $condition;

        return $this;
    }

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
