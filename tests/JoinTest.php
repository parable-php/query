<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;
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

    public function testOnCallable()
    {
        $join = new Join('table', 't');

        $join->onCallable(function(Query $query) {
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

    public function testOrOnCallable()
    {
        $join = new Join('table', 't');

        $join->orOnCallable(function(Query $query) {
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

    public function testOnCondition()
    {
        $join = new Join('table', 't');

        $join->onCondition(new ValueCondition('table', 'user_id', '=', 'u.id', AbstractCondition::TYPE_AND, true));
        $join->onCondition(new CallableCondition(function(Query $query) {
            $query->where('test', '=', 1);
        }));

        $onConditions = $join->getOnConditions();

        self::assertCount(2, $onConditions);

        /** @var CallableCondition $condition */
        $valueCondition = $onConditions[0];
        $callableCondition = $onConditions[1];

        self::assertInstanceOf(ValueCondition::class, $valueCondition);
        self::assertInstanceOf(CallableCondition::class, $callableCondition);
    }

    public function testOnNullCondition()
    {
        $join = new Join('table', 't');

        $join->onNull('test');

        /** @var ValueCondition $onCondition */
        $onCondition = $join->getOnConditions()[0];

        self::assertInstanceOf(ValueCondition::class, $onCondition);
        self::assertSame('IS NULL', $onCondition->getComparator());
        self::assertNull($onCondition->getValue());
    }

    public function testOnNotNullCondition()
    {
        $join = new Join('table', 't');

        $join->onNotNull('test');

        /** @var ValueCondition $onCondition */
        $onCondition = $join->getOnConditions()[0];

        self::assertInstanceOf(ValueCondition::class, $onCondition);
        self::assertSame('IS NOT NULL', $onCondition->getComparator());
        self::assertNull($onCondition->getValue());
    }
}
