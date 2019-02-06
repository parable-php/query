<?php

namespace Parable\Query\Tests;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;
use Parable\Query\Exception;
use Parable\Query\Join;
use Parable\Query\Query;

class JoinTest extends \PHPUnit\Framework\TestCase
{
    public function testBasicJoinCreation()
    {
        $join = new Join('table', 't');

        self::assertSame('table', $join->getTableName());
        self::assertSame('t', $join->getTableAlias());
    }

    public function testGetTableAliasOrName()
    {
        $join = new Join('table');

        self::assertSame('table', $join->getTableAliasOrName());

        $join = new Join('table', 't');

        self::assertSame('t', $join->getTableAliasOrName());
    }

    public function testOnWithValueCondition()
    {
        $join = new Join('table', 't');

        $join->on('username', '=', 'amy');

        $onConditions = $join->getOnConditions();

        self::assertCount(1, $onConditions);

        /** @var ValueCondition $condition */
        $condition = $onConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('=', $condition->getComparator());
        self::assertSame('amy', $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOnKeyValue()
    {
        $join = new Join('table', 't');

        $join->onKey('username', '=', 't.username');

        $onConditions = $join->getOnConditions();

        self::assertCount(1, $onConditions);

        /** @var ValueCondition $condition */
        $condition = $onConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('=', $condition->getComparator());
        self::assertSame('t.username', $condition->getValue());
        self::assertTrue($condition->isValueKey());
    }


    public function testOrOnWithValueCondition()
    {
        $join = new Join('table', 't');

        $join->orOn('username', '=', 'amy');

        $onConditions = $join->getOnConditions();

        self::assertCount(1, $onConditions);

        /** @var ValueCondition $condition */
        $condition = $onConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('=', $condition->getComparator());
        self::assertSame('amy', $condition->getValue());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrOnKeyValue()
    {
        $join = new Join('table', 't');

        $join->orOnKey('username', '=', 't.username');

        $onConditions = $join->getOnConditions();

        self::assertCount(1, $onConditions);

        /** @var ValueCondition $condition */
        $condition = $onConditions[0];

        self::assertInstanceOf(ValueCondition::class, $condition);
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertSame('username', $condition->getKey());
        self::assertSame('=', $condition->getComparator());
        self::assertSame('t.username', $condition->getValue());
        self::assertTrue($condition->isValueKey());
    }

    public function testWhereCallable()
    {
        $join = new Join('table', 't');

        $join->whereCallable(function(Query $query) {
            return $query->getTableName();
        });

        $onConditions = $join->getOnConditions();

        self::assertCount(1, $onConditions);

        /** @var CallableCondition $condition */
        $condition = $onConditions[0];

        self::assertInstanceOf(CallableCondition::class, $condition);

        $callable = $condition->getCallable();

        self::assertIsCallable($callable);
        self::assertSame('query', $callable(Query::select('query', 'q')));
        self::assertSame(AbstractCondition::TYPE_AND, $condition->getType());
        self::assertFalse($condition->isValueKey());
    }

    public function testOrWhereCallable()
    {
        $join = new Join('table', 't');

        $join->orWhereCallable(function(Query $query) {
            return $query->getTableName();
        });

        $onConditions = $join->getOnConditions();

        self::assertCount(1, $onConditions);

        /** @var CallableCondition $condition */
        $condition = $onConditions[0];

        self::assertInstanceOf(CallableCondition::class, $condition);

        $callable = $condition->getCallable();

        self::assertIsCallable($callable);
        self::assertSame('query', $callable(Query::select('query', 'q')));
        self::assertSame(AbstractCondition::TYPE_OR, $condition->getType());
        self::assertFalse($condition->isValueKey());
    }
}
