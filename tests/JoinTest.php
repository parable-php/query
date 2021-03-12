<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Conditions\AbstractCondition;
use Parable\Query\Conditions\CallableCondition;
use Parable\Query\Conditions\ValueCondition;
use Parable\Query\Join;
use Parable\Query\Query;
use PHPUnit\Framework\TestCase;

class JoinTest extends TestCase
{
    public function testBasicJoinCreation(): void
    {
        $join = new Join('table', 't');

        self::assertSame('table', $join->getTableName());
        self::assertSame('t', $join->getTableAlias());
    }

    public function testGetTableAliasOrName(): void
    {
        $join = new Join('table');

        self::assertSame('table', $join->getTableAliasOrName());

        $join = new Join('table', 't');

        self::assertSame('t', $join->getTableAliasOrName());
    }

    public function testOnWithValueCondition(): void
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

    public function testOnKeyValue(): void
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

    public function testOrOnWithValueCondition(): void
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

    public function testOrOnKeyValue(): void
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

    public function testOnCallable(): void
    {
        $join = new Join('table', 't');

        $join->onCallable(static function(Query $query) {
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

    public function testOrOnCallable(): void
    {
        $join = new Join('table', 't');

        $join->orOnCallable(static function(Query $query) {
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

    public function testOnCondition(): void
    {
        $join = new Join('table', 't');

        $join->onCondition(new ValueCondition('table', 'user_id', '=', 'u.id', AbstractCondition::TYPE_AND, true));
        $join->onCondition(new CallableCondition(static function(Query $query) {
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

    public function testOnNullCondition(): void
    {
        $join = new Join('table', 't');

        $join->onNull('test');

        /** @var ValueCondition $onCondition */
        $onCondition = $join->getOnConditions()[0];

        self::assertInstanceOf(ValueCondition::class, $onCondition);
        self::assertSame('IS NULL', $onCondition->getComparator());
        self::assertNull($onCondition->getValue());
    }

    public function testOnNotNullCondition(): void
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
