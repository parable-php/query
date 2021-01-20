<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;
use Parable\Query\Join;
use Parable\Query\OrderBy;
use Parable\Query\Query;
use Parable\Query\ValueSet;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testBasicQueryCreation(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertSame('SELECT', $query->getType());
        self::assertSame('table', $query->getTableName());
        self::assertSame('t', $query->getTableAlias());
    }

    public function testQueryDoesntCareAboutType(): void
    {
        $query = new Query('NOPE', 'table', 't');

        self::assertSame('NOPE', $query->getType());
    }

    public function testSpecificDeleteQueryCreation(): void
    {
        $query = Query::delete('table');

        self::assertSame('DELETE', $query->getType());
        self::assertSame('table', $query->getTableName());
    }

    public function testSpecificInsertQueryCreation(): void
    {
        $query = Query::insert('table');

        self::assertSame('INSERT', $query->getType());
        self::assertSame('table', $query->getTableName());
    }

    public function testSpecificSelectQueryCreation(): void
    {
        $query = Query::select('table', 't');

        self::assertSame('SELECT', $query->getType());
        self::assertSame('table', $query->getTableName());
        self::assertSame('t', $query->getTableAlias());
    }

    public function testSpecificUpdateQueryCreation(): void
    {
        $query = Query::update('table');

        self::assertSame('UPDATE', $query->getType());
        self::assertSame('table', $query->getTableName());
    }

    public function testGetTableAliasOrName(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table');

        self::assertSame('table', $query->getTableAliasOrName());

        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertSame('t', $query->getTableAliasOrName());
    }

    public function testGetColumnsEmptyByDefault(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertEmpty($query->getColumns());
    }

    public function testGetSetColumns(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->setColumns('username', 'email');

        self::assertSame(
            ['username', 'email'],
            $query->getColumns()
        );
    }

    public function testWhere(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->where('username', '=', 'amy');

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var ValueCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('=', $condition->getComparator());
        self::assertSame('amy', $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testWhereNull(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->whereNull('username');

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var ValueCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('IS NULL', $condition->getComparator());
        self::assertNull($condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testWhereNotNull(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->whereNotNull('username');

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var ValueCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('IS NOT NULL', $condition->getComparator());
        self::assertNull($condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhere(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orWhere('username', '=', 'amy');

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var ValueCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('=', $condition->getComparator());
        self::assertSame('amy', $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhereNull(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orWhereNull('username');

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var ValueCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('IS NULL', $condition->getComparator());
        self::assertNull($condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhereNotNull(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orWhereNotNull('username');

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var ValueCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('IS NOT NULL', $condition->getComparator());
        self::assertNull($condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testWhereCallable(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->whereCallable(static function(Query $query) {
            return $query->getTableName();
        });

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var CallableCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(CallableCondition::class, $condition);

        $callable = $condition->getCallable();

        self::assertIsCallable($callable);
        self::assertSame('query', $callable(Query::select('query', 'q')));
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhereCallable(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orWhereCallable(static function(Query $query) {
            return $query->getTableName();
        });

        $whereConditions = $query->getWhereConditions();

        self::assertCount(1, $whereConditions);

        /** @var CallableCondition $condition */
        $condition = $whereConditions[0];

        self::assertInstanceOf(CallableCondition::class, $condition);

        $callable = $condition->getCallable();

        self::assertIsCallable($callable);
        self::assertSame('query', $callable(Query::select('query', 'q')));
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertFalse($condition->isValueKey());
    }

    public function testWhereCondition(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->whereCondition(new ValueCondition('table', 'user_id', '=', 'u.id'));
        $query->whereCondition(new CallableCondition(static function(Query $query) {
            $query->where('test', '=', 1);
        }));

        $whereConditions = $query->getWhereConditions();

        self::assertCount(2, $whereConditions);

        /** @var CallableCondition $condition */
        $valueCondition = $whereConditions[0];
        $callableCondition = $whereConditions[1];

        self::assertInstanceOf(ValueCondition::class, $valueCondition);
        self::assertInstanceOf(CallableCondition::class, $callableCondition);
    }

    public function testLimitAndOffset(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->limit(10, 5);

        self::assertSame(10, $query->getLimit());
        self::assertSame(5, $query->getOffset());
    }

    public function testLimitAndOffsetSetTo0MeansNoLimitAndOffset(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertNull($query->getLimit());
        self::assertNull($query->getOffset());

        $query->limit(10, 5);

        self::assertSame(10, $query->getLimit());
        self::assertSame(5, $query->getOffset());

        $query->limit(0, 0);

        self::assertNull($query->getLimit());
        self::assertNull($query->getOffset());
    }

    public function testForceIndex(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertNull($query->getForceIndex());

        $query->forceIndex('id');

        self::assertSame('id', $query->getForceIndex());
    }

    public function testGroupBy(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->groupBy('username');

        self::assertSame(
            ['username'],
            $query->getGroupBy()
        );
    }

    public function testOrderByIsAscendingByDefault(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orderBy(OrderBy::asc('username'));

        self::assertCount(1, $query->getOrderBy());
        self::assertTrue($query->getOrderBy()[0]->isAscending());
        self::assertSame(['username'], $query->getOrderBy()[0]->getKeys());
    }

    public function testOrderByDescending(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orderBy(OrderBy::desc('username'));

        self::assertCount(1, $query->getOrderBy());
        self::assertTrue($query->getOrderBy()[0]->isDescending());
        self::assertSame(['username'], $query->getOrderBy()[0]->getKeys());
    }

    public function testInnerJoin(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');
        $join = new Join('join_table', 'jt');

        $query->innerJoin($join);

        self::assertEmpty($query->getJoinsByType(Query::JOIN_TYPE_LEFT));
        self::assertCount(1, $query->getJoinsByType(Query::JOIN_TYPE_INNER));

        $joinFromQuery = $query->getJoinsByType(Query::JOIN_TYPE_INNER)[0];

        self::assertSame($join, $joinFromQuery);
    }

    public function testLeftJoin(): void
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');
        $join = new Join('join_table', 'jt');

        $query->leftJoin($join);

        self::assertEmpty($query->getJoinsByType(Query::JOIN_TYPE_INNER));
        self::assertCount(1, $query->getJoinsByType(Query::JOIN_TYPE_LEFT));

        $joinFromQuery = $query->getJoinsByType(Query::JOIN_TYPE_LEFT)[0];

        self::assertSame($join, $joinFromQuery);
    }

    public function testCountValueSets(): void
    {
        $query = new Query(Query::TYPE_UPDATE, 'table', 't');

        self::assertSame(0, $query->countValueSets());

        $query->addValueSet(new ValueSet([]));

        self::assertSame(1, $query->countValueSets());
    }

    public function testHasValueSets(): void
    {
        $query = new Query(Query::TYPE_UPDATE, 'table', 't');

        self::assertFalse($query->hasValueSets());

        $query->addValueSet(new ValueSet([]));

        self::assertTrue($query->hasValueSets());
    }
}
