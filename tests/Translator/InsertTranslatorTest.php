<?php declare(strict_types=1);

namespace Parable\Query\Tests\Translator;

use Parable\Query\QueryException;
use Parable\Query\Query;
use Parable\Query\Translators\InsertTranslator;
use Parable\Query\Translators\Traits\HasConditionsTrait;
use Parable\Query\Translators\Traits\SupportsForceIndexTrait;
use Parable\Query\Translators\Traits\SupportsGroupByTrait;
use Parable\Query\Translators\Traits\SupportsJoinTrait;
use Parable\Query\Translators\Traits\SupportsLimitTrait;
use Parable\Query\Translators\Traits\SupportsOrderByTrait;
use Parable\Query\Translators\Traits\SupportsValuesTrait;
use Parable\Query\Translators\Traits\SupportsWhereTrait;
use Parable\Query\ValueSet;
use PDO;
use PHPUnit\Framework\TestCase;

class InsertTranslatorTest extends TestCase
{
    protected InsertTranslator $translator;

    public function setUp(): void
    {
        $this->translator = new InsertTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet(): void
    {
        $traits = class_uses($this->translator);

        self::assertContains(SupportsValuesTrait::class, $traits);

        self::assertNotContains(HasConditionsTrait::class, $traits);
        self::assertNotContains(SupportsForceIndexTrait::class, $traits);
        self::assertNotContains(SupportsJoinTrait::class, $traits);
        self::assertNotContains(SupportsWhereTrait::class, $traits);
        self::assertNotContains(SupportsGroupByTrait::class, $traits);
        self::assertNotContains(SupportsOrderByTrait::class, $traits);
        self::assertNotContains(SupportsLimitTrait::class, $traits);
    }

    /**
     * @dataProvider dpTranslatorTypes
     */
    public function testTranslatorAcceptsCorrectly($type, $accepts): void
    {
        $query = new Query($type, 'table', 't');

        self::assertSame($accepts, $this->translator->accepts($query));
    }

    public function dpTranslatorTypes(): array
    {
        return [
            [Query::TYPE_DELETE, false],
            [Query::TYPE_INSERT, true],
            [Query::TYPE_SELECT, false],
            [Query::TYPE_UPDATE, false],
        ];
    }

    public function testInsertBasicQuery(): void
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

    public function testInsertWithMultipleValues(): void
    {
        $query = Query::insert('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test1',
        ]));
        $query->addValueSet(new ValueSet([
            'username' => 'test2',
        ]));

        self::assertSame(
            "INSERT INTO `table` (`username`) VALUES ('test1'), ('test2')",
            $this->translator->translate($query)
        );
    }

    public function testNoValueSetsBreaks(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Insert queries must contain at least one value set.');

        $query = Query::insert('table', 't');

        $this->translator->translate($query);
    }

    public function testMultipleNonMatchingValueSetsBreaks(): void
    {
        $this->expectException(QueryException::class);
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
