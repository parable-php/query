<?php

namespace Parable\Query\Tests;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;
use Parable\Query\Join;
use Parable\Query\Query;
use Parable\Query\ValueSet;

class QueryTest extends \PHPUnit\Framework\TestCase
{
    public function testBasicQueryCreation()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertSame('SELECT', $query->getType());
        self::assertSame('table', $query->getTableName());
        self::assertSame('t', $query->getTableAlias());
    }

    public function testQueryDoesntCareAboutType()
    {
        $query = new Query('NOPE', 'table', 't');

        self::assertSame('NOPE', $query->getType());
    }

    public function testSpecificDeleteQueryCreation()
    {
        $query = Query::delete('table', 't');

        self::assertSame('DELETE', $query->getType());
        self::assertSame('table', $query->getTableName());
        self::assertSame('t', $query->getTableAlias());
    }

    public function testSpecificInsertQueryCreation()
    {
        $query = Query::insert('table');

        self::assertSame('INSERT', $query->getType());
        self::assertSame('table', $query->getTableName());
    }

    public function testSpecificSelectQueryCreation()
    {
        $query = Query::select('table', 't');

        self::assertSame('SELECT', $query->getType());
        self::assertSame('table', $query->getTableName());
        self::assertSame('t', $query->getTableAlias());
    }

    public function testSpecificUpdateQueryCreation()
    {
        $query = Query::update('table');

        self::assertSame('UPDATE', $query->getType());
        self::assertSame('table', $query->getTableName());
    }

    public function testGetTableAliasOrName()
    {
        $query = new Query(Query::TYPE_SELECT, 'table');

        self::assertSame('table', $query->getTableAliasOrName());

        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertSame('t', $query->getTableAliasOrName());
    }

    public function testGetColumnsEmptyByDefault()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        self::assertEmpty($query->getColumns());
    }

    public function testGetSetColumns()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->setColumns(['username', 'email']);

        self::assertSame(
            ['username', 'email'],
            $query->getColumns()
        );
    }

    public function testWhere()
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

    public function testWhereNull()
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
        self::assertSame(null, $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testWhereNotNull()
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
        self::assertSame(null, $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhere()
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

    public function testOrWhereNull()
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
        self::assertSame(null, $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhereNotNull()
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
        self::assertSame(null, $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testWhereCallable()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->whereCallable(function(Query $query) {
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

    public function testOrWhereCallable()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orWhereCallable(function(Query $query) {
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

    public function testWhereCondition()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->whereCondition(new ValueCondition('table', 'user_id', '=', 'u.id'));
        $query->whereCondition(new CallableCondition(function(Query $query) {
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

    public function testLimitAndOffset()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->limit(10, 5);

        self::assertSame(10, $query->getLimit());
        self::assertSame(5, $query->getOffset());
    }

    public function testLimitAndOffsetSetTo0MeansNoLimitAndOffset()
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

    public function testGroupBy()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->groupBy(['username']);

        self::assertSame(
            ['username'],
            $query->getGroupBy()
        );
    }

    public function testOrderByIsAscendingByDefault()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orderBy('username');

        self::assertSame(
            [
                'username' => Query::ORDER_ASC,
            ],
            $query->getOrderBy()
        );
    }

    public function testOrderByDescending()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');

        $query->orderBy('username', Query::ORDER_DESC);

        self::assertSame(
            [
                'username' => Query::ORDER_DESC,
            ],
            $query->getOrderBy()
        );
    }

    public function testInnerJoin()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');
        $join = new Join('join_table', 'jt');

        $query->innerJoin($join);

        self::assertEmpty($query->getJoinsByType(Query::JOIN_TYPE_LEFT));
        self::assertCount(1, $query->getJoinsByType(Query::JOIN_TYPE_INNER));

        $joinFromQuery = $query->getJoinsByType(Query::JOIN_TYPE_INNER)[0];

        self::assertSame($join, $joinFromQuery);
    }

    public function testLeftJoin()
    {
        $query = new Query(Query::TYPE_SELECT, 'table', 't');
        $join = new Join('join_table', 'jt');

        $query->leftJoin($join);

        self::assertEmpty($query->getJoinsByType(Query::JOIN_TYPE_INNER));
        self::assertCount(1, $query->getJoinsByType(Query::JOIN_TYPE_LEFT));

        $joinFromQuery = $query->getJoinsByType(Query::JOIN_TYPE_LEFT)[0];

        self::assertSame($join, $joinFromQuery);
    }

    public function testHasValueSets()
    {
        $query = new Query(Query::TYPE_UPDATE, 'table', 't');

        self::assertFalse($query->hasValueSets());

        $query->addValueSet(new ValueSet([]));

        self::assertTrue($query->hasValueSets());
    }
}
