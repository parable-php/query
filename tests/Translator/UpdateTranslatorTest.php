<?php declare(strict_types=1);

namespace Parable\Query\Tests\Translator;

use Parable\Query\Exception;
use Parable\Query\Query;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsForceIndexTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\Translator\UpdateTranslator;
use Parable\Query\ValueSet;
use PDO;
use PHPUnit\Framework\TestCase;

class UpdateTranslatorTest extends TestCase
{
    /**
     * @var UpdateTranslator
     */
    protected $translator;

    public function setUp(): void
    {
        $this->translator = new UpdateTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet(): void
    {
        $traits = class_uses($this->translator);

        self::assertContains(HasConditionsTrait::class, $traits);
        self::assertContains(SupportsWhereTrait::class, $traits);
        self::assertContains(SupportsValuesTrait::class, $traits);
        self::assertContains(SupportsJoinTrait::class, $traits);

        self::assertNotContains(SupportsForceIndexTrait::class, $traits);
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
            [Query::TYPE_INSERT, false],
            [Query::TYPE_SELECT, false],
            [Query::TYPE_UPDATE, true],
        ];
    }

    public function testBasicQuery(): void
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

    public function testNoValueSetsBreaks(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Update queries must contain exactly one value set, 0 provided.');

        $query = Query::update('table');

        $this->translator->translate($query);
    }

    public function testMultipleValueSetsBreaks(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Update queries must contain exactly one value set, 2 provided.');

        $query = Query::update('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        $this->translator->translate($query);
    }

    public function testNullValueIsParsedCorrectly(): void
    {
        $query = Query::update('table');
        $query->addValueSet(new ValueSet([
            'username' => null,
        ]));

        self::assertSame(
            $this->translator->translate($query),
            'UPDATE `table` SET `username` = NULL'
        );
    }
}
