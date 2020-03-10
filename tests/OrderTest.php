<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Exception;
use Parable\Query\OrderBy;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testOrderRequiresKeys(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot create order without keys.');

        OrderBy::asc();
    }

    public function testOrderByAscending(): void
    {
        $orderBy = OrderBy::asc('key');

        self::assertTrue($orderBy->isAscending());
        self::assertFalse($orderBy->isDescending());

        self::assertSame('ASC', $orderBy->getDirection());

        self::assertSame(['key'], $orderBy->getKeys());
    }

    public function testOrderByDescending(): void
    {
        $orderBy = OrderBy::desc('key');

        self::assertTrue($orderBy->isDescending());
        self::assertFalse($orderBy->isAscending());

        self::assertSame('DESC', $orderBy->getDirection());

        self::assertSame(['key'], $orderBy->getKeys());
    }

    public function testMultipleKeys(): void
    {
        $orderBy = OrderBy::asc('id', 'updated_at');

        self::assertSame(['id', 'updated_at'], $orderBy->getKeys());
    }
}
