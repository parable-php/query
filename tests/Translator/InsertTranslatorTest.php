<?php

namespace Parable\Query\Tests\Translator;

use Parable\Query\Exception;
use Parable\Query\Query;
use Parable\Query\Translator\InsertTranslator;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\ValueSet;
use PDO;

class InsertTranslatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InsertTranslator
     */
    protected $translator;

    public function setUp()
    {
        $this->translator = new InsertTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet()
    {
        $traits = class_uses($this->translator);

        self::assertContains(SupportsValuesTrait::class, $traits);

        self::assertNotContains(HasConditionsTrait::class, $traits);
        self::assertNotContains(SupportsJoinTrait::class, $traits);
        self::assertNotContains(SupportsWhereTrait::class, $traits);
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
            [Query::TYPE_INSERT, true],
            [Query::TYPE_SELECT, false],
            [Query::TYPE_UPDATE, false],
        ];
    }

    public function testInsertBasicQuery()
    {
        $query = Query::insert('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "INSERT INTO `table` (`username`) VALUES ('test')",
            $this->translator->translate($query)
        );
    }

    public function testNoValueSetsBreaks()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insert queries must contain at least one value set.');

        $query = Query::insert('table', 't');

        $this->translator->translate($query);
    }

    public function testMultipleNonMatchingValueSetsBreaks()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not all value sets match on keys: username');

        $query = Query::insert('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));
        $query->addValueSet(new ValueSet([
            'password' => 'test',
        ]));

        $this->translator->translate($query);
    }
}
