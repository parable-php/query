<?php

namespace Parable\Query\Tests;

use Parable\Query\ValueSet;
use stdClass;

class ValueSetTest extends \PHPUnit\Framework\TestCase
{
    public function testValueSetCreation()
    {
        $valueSet = new ValueSet([
            'username' => 'amy',
        ]);

        self::assertSame(
            ['username' => 'amy'],
            $valueSet->getValues()
        );
    }

    public function testValueGetKeys()
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
    public function testValueSetCreationFailsOnNonScalarValue($value, $expectedMessage)
    {
        $this->expectException(\Parable\Query\Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        new ValueSet([
            'username' => $value,
        ]);
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
