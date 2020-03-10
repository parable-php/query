<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Exception;
use Parable\Query\StringBuilder;
use PHPUnit\Framework\TestCase;

class StringBuilderTest extends TestCase
{
    public function testBasicUsageWithDefaultGlue(): void
    {
        $parts = new StringBuilder();

        $parts->add('1');
        $parts->add('2');

        self::assertSame('1 2', $parts->toString());
    }

    public function testFromArray(): void
    {
        $parts = StringBuilder::fromArray(['1', '2']);

        self::assertSame('1 2', $parts->toString());
    }

    public function testPrependActuallyPrepends(): void
    {
        $parts = new StringBuilder();

        $parts->add('1');
        $parts->add('2');
        $parts->prepend('3');

        self::assertSame('3 1 2', $parts->toString());
    }

    public function testMergeWorksCorrectly(): void
    {
        $parts1 = StringBuilder::fromArray(['1', '2']);
        $parts2 = StringBuilder::fromArray(['3', '4']);

        $partsMerged = $parts1->merge($parts2);

        self::assertSame('1 2 3 4', $partsMerged->toString());
    }

    public function testMergeBreaksWhenDifferentGluesDetected(): void
    {
        $parts1 = StringBuilder::fromArray(['1', '2'], ' ');
        $parts2 = StringBuilder::fromArray(['3', '4'], ',');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot merge StringBuilder with different glues.');

        $parts1->merge($parts2);
    }
}
