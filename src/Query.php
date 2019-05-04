<?php declare(strict_types=1);

namespace Parable\Query;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;

class Query
{
    public const TYPE_DELETE = 'DELETE';
    public const TYPE_INSERT = 'INSERT';
    public const TYPE_UPDATE = 'UPDATE';
    public const TYPE_SELECT = 'SELECT';

    public const JOIN_TYPE_INNER = 'INNER';
    public const JOIN_TYPE_LEFT = 'LEFT';

    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    protected const VALID_TYPES = [
        self::TYPE_DELETE,
        self::TYPE_INSERT,
        self::TYPE_UPDATE,
        self::TYPE_SELECT,
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string|null
     */
    protected $tableAlias;

    /**
     * @var string[]
     */
    protected $columns = [];

    /**
     * @var ValueCondition[]|CallableCondition[]
     */
    protected $whereConditions = [];

    /**
     * @var Join[][]
     */
    protected $joins = [];

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int|null
     */
    protected $offset;

    /**
     * @var string[]
     */
    protected $groupBy = [];

    /**
     * @var string[]
     */
    protected $orderBy = [];

    /**
     * @var ValueSet[]
     */
    protected $valueSets = [];

    public function __construct(
        string $type,
        string $tableName,
        ?string $tableAlias = null
    ) {
        $this->type = $type;
        $this->tableName = $tableName;
        $this->tableAlias = $tableAlias;
    }

    public function getType(): string
    {
        return $this->type;
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
        return $this->tableAlias ?? $this->tableName;
    }

    public function setColumns(string ...$columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function where(string $key, string $comparator, $value): self
    {
        $this->whereConditions[] = $this->createValueCondition(
            $this->tableName,
            $key,
            $comparator,
            $value,
            'AND'
        );

        return $this;
    }

    public function whereNull(string $key): self
    {
        $this->whereConditions[] = $this->createValueCondition(
            $this->tableName,
            $key,
            'IS NULL',
            null,
            'AND'
        );

        return $this;
    }

    public function whereNotNull(string $key): self
    {
        $this->whereConditions[] = $this->createValueCondition(
            $this->tableName,
            $key,
            'IS NOT NULL',
            null,
            'AND'
        );

        return $this;
    }

    public function orWhere(string $key, string $comparator, $value): self
    {
        $this->whereConditions[] = $this->createValueCondition(
            $this->tableName,
            $key,
            $comparator,
            (string)$value,
            'OR'
        );

        return $this;
    }

    public function orWhereNull(string $key): self
    {
        $this->whereConditions[] = $this->createValueCondition(
            $this->tableName,
            $key,
            'IS NULL',
            null,
            'OR'
        );

        return $this;
    }

    public function orWhereNotNull(string $key): self
    {
        $this->whereConditions[] = $this->createValueCondition(
            $this->tableName,
            $key,
            'IS NOT NULL',
            null,
            'OR'
        );

        return $this;
    }

    public function whereCallable(callable $callable): self
    {
        $this->whereConditions[] = new CallableCondition($callable, 'AND');

        return $this;
    }

    public function orWhereCallable(callable $callable): self
    {
        $this->whereConditions[] = new CallableCondition($callable, 'OR');

        return $this;
    }

    public function whereCondition(AbstractCondition $condition): self
    {
        $this->whereConditions[] = $condition;

        return $this;
    }

    public function hasWhereConditions(): bool
    {
        return count($this->whereConditions) > 0;
    }

    public function getWhereConditions(): array
    {
        return $this->whereConditions;
    }

    public function innerJoin(Join $join): self
    {
        $this->joins[self::JOIN_TYPE_INNER][] = $join;

        return $this;
    }

    public function leftJoin(Join $join): self
    {
        $this->joins[self::JOIN_TYPE_LEFT][] = $join;

        return $this;
    }

    public function hasJoins(): bool
    {
        return count($this->joins, COUNT_RECURSIVE) > 0;
    }

    /**
     * @return Join[]
     */
    public function getJoinsByType(string $type): array
    {
        return $this->joins[$type] ?? [];
    }

    public function limit(int $limit, int $offset = null): self
    {
        if ($limit === 0) {
            $limit = null;
        }

        if ($offset === 0) {
            $offset = null;
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @param string[] $keys
     */
    public function groupBy(string ...$keys): self
    {
        $this->groupBy = $keys;

        return $this;
    }

    public function hasGroupBy(): bool
    {
        return count($this->groupBy) > 0;
    }

    /**
     * @return string[]
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    public function orderBy(OrderBy $orderBy): self
    {
        $this->orderBy[] = $orderBy;

        return $this;
    }

    public function hasOrderBy(): bool
    {
        return count($this->orderBy) > 0;
    }

    /**
     * @return OrderBy[]
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function addValueSet(ValueSet $valueSet): self
    {
        $this->valueSets[] = $valueSet;

        return $this;
    }

    public function countValueSets(): int
    {
        return count($this->getValueSets());
    }

    public function hasValueSets(): bool
    {
        return $this->countValueSets() > 0;
    }

    /**
     * @return ValueSet[]
     */
    public function getValueSets(): array
    {
        return $this->valueSets;
    }

    public function createCleanClone(): self
    {
        return new self($this->getType(), $this->getTableName(), $this->getTableAlias());
    }

    protected function createValueCondition(
        string $tableName,
        string $key,
        string $comparator,
        $values = null,
        ?string $type = null
    ): ValueCondition {
        return new ValueCondition(
            $tableName,
            $key,
            $comparator,
            $values,
            $type
        );
    }

    public static function delete(string $tableName, ?string $tableAlias = null): self
    {
        return new self(self::TYPE_DELETE, $tableName, $tableAlias);
    }

    public static function insert(string $tableName): self
    {
        return new self(self::TYPE_INSERT, $tableName);
    }

    public static function update(string $tableName, ?string $tableAlias = null): self
    {
        return new self(self::TYPE_UPDATE, $tableName, $tableAlias);
    }

    public static function select(string $tableName, ?string $tableAlias = null): self
    {
        return new self(self::TYPE_SELECT, $tableName, $tableAlias);
    }
}
