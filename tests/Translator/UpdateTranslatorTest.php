<?php

namespace Parable\Query\Tests\Translator;

use Parable\Query\Exception;
use Parable\Query\Query;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\Translator\UpdateTranslator;
use Parable\Query\ValueSet;
use PDO;

class UpdateTranslatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpdateTranslator
     */
    protected $translator;

    public function setUp()
    {
        $this->translator = new UpdateTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet()
    {
        $traits = class_uses($this->translator);

        self::assertContains(HasConditionsTrait::class, $traits);
        self::assertContains(SupportsWhereTrait::class, $traits);
        self::assertContains(SupportsValuesTrait::class, $traits);
        self::assertContains(SupportsJoinTrait::class, $traits);

        self::assertNotContains(SupportsGroupByTrait::class, $traits);
        self::assertNotContains(SupportsOrderByTrait::class, $traits);
        self::assertNotContains(SupportsLimitTrait::class, $traits);
    }

    /**
     * @dataProvider dpTranslatorTypes
     */
    public function testTranslatorAcceptsCorrectly($type, $accepts)
    {
        $query = new Query($type, 'table', 't');

        self::assertSame($accepts, $this->translator->accepts($query));
    }

    public function dpTranslatorTypes()
    {
        return [
            [Query::TYPE_DELETE, false],
            [Query::TYPE_INSERT, false],
            [Query::TYPE_SELECT, false],
            [Query::TYPE_UPDATE, true],
        ];
    }

    public function testBasicQuery()
    {
        $query = Query::update('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "UPDATE `table` SET `username` = 'test'",
            $this->translator->translate($query)
        );
    }

    public function testBasicQueryWithAlias()
    {
        $query = Query::update('table', 't');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "UPDATE `table` `t` SET `username` = 'test'",
            $this->translator->translate($query)
        );
    }

    public function testNoValueSetsBreaks()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Update queries must contain exactly one value set, 0 provided.');

        $query = Query::update('table', 't');

        $this->translator->translate($query);
    }

    public function testMultipleValueSetsBreaks()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Update queries must contain exactly one value set, 2 provided.');

        $query = Query::update('table', 't');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        $this->translator->translate($query);
    }
}
