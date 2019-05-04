<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Order;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    public function testOrderByAscending(): void
    {
        $orderBy = Order::asc('key');

        self::assertTrue($orderBy->isAscending());
        self::assertFalse($orderBy->isDescending());

        self::assertSame('ASC', $orderBy->getDirectionAsString());

        self::assertSame(['key'], $orderBy->getKeys());
    }

    public function testOrderByDescending(): void
    {
        $orderBy = Order::desc('key');

        self::assertTrue($orderBy->isDescending());
        self::assertFalse($orderBy->isAscending());

        self::assertSame('DESC', $orderBy->getDirectionAsString());

        self::assertSame(['key'], $orderBy->getKeys());
    }

    public function testMultipleKeys(): void
    {
        $orderBy = Order::asc('id', 'updated_at');

        self::assertSame(['id', 'updated_at'], $orderBy->getKeys());
    }
}
