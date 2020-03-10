<?php declare(strict_types=1);

namespace Parable\Query\Tests\Translator;

use Parable\Query\Query;
use Parable\Query\Translator\DeleteTranslator;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsForceIndexTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use PDO;
use PHPUnit\Framework\TestCase;

class DeleteTranslatorTest extends TestCase
{
    /**
     * @var DeleteTranslator
     */
    protected $translator;

    public function setUp(): void
    {
        $this->translator = new DeleteTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet(): void
    {
        $traits = class_uses($this->translator);

        self::assertContains(HasConditionsTrait::class, $traits);
        self::assertContains(SupportsJoinTrait::class, $traits);
        self::assertContains(SupportsWhereTrait::class, $traits);
        self::assertContains(SupportsGroupByTrait::class, $traits);
        self::assertContains(SupportsOrderByTrait::class, $traits);
        self::assertContains(SupportsLimitTrait::class, $traits);

        self::assertNotContains(SupportsForceIndexTrait::class, $traits);
        self::assertNotContains(SupportsValuesTrait::class, $traits);
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
            [Query::TYPE_DELETE, true],
            [Query::TYPE_INSERT, false],
            [Query::TYPE_SELECT, false],
            [Query::TYPE_UPDATE, false],
        ];
    }

    public function testDeleteBasicQuery(): void
    {
        $query = Query::delete('table');

        self::assertSame(
            "DELETE FROM `table`",
            $this->translator->translate($query)
        );
    }
}
