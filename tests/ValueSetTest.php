<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Exception;
use Parable\Query\ValueSet;
use PHPUnit\Framework\TestCase;
use stdClass;

class ValueSetTest extends TestCase
{
    public function testValueSetCreation(): void
    {
        $valueSet = new ValueSet([
            'username' => 'amy',
        ]);

        self::assertSame(
            ['username' => 'amy'],
            $valueSet->getValues()
        );
    }

    public function testValueGetKeys(): void
    {
        $valueSet = new ValueSet([
            'username' => 'amy',
        ]);

        self::assertSame(
            ['username'],
            $valueSet->getKeys()
        );
    }

    /**
     * @dataProvider dpInvalidValuesForSet
     */
    public function testValueSetCreationFailsOnNonScalarValue($value, string $expectedMessage): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        new ValueSet([
            'username' => $value,
        ]);
    }

    public function testHasValues(): void
    {
        $valueSet = new ValueSet([]);

        self::assertFalse($valueSet->hasValues());

        $valueSet->addValue('test', 'true');

        self::assertTrue($valueSet->hasValues());
    }

    public function dpInvalidValuesForSet(): array
    {
        return [
            [
                [], 'Value is of invalid type: array'
            ],
            [
                new stdClass(), 'Value is of invalid type: object'
            ],
        ];
    }
}
