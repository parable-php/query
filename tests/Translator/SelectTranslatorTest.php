<?php declare(strict_types=1);

namespace Parable\Query\Tests\Translator;

use Parable\Query\QueryException;
use Parable\Query\Join;
use Parable\Query\OrderBy;
use Parable\Query\Query;
use Parable\Query\Translators\SelectTranslator;
use Parable\Query\Translators\Traits\HasConditionsTrait;
use Parable\Query\Translators\Traits\SupportsForceIndexTrait;
use Parable\Query\Translators\Traits\SupportsGroupByTrait;
use Parable\Query\Translators\Traits\SupportsJoinTrait;
use Parable\Query\Translators\Traits\SupportsLimitTrait;
use Parable\Query\Translators\Traits\SupportsOrderByTrait;
use Parable\Query\Translators\Traits\SupportsValuesTrait;
use Parable\Query\Translators\Traits\SupportsWhereTrait;
use PDO;
use PHPUnit\Framework\TestCase;

class SelectTranslatorTest extends TestCase
{
    protected SelectTranslator $translator;

    public function setUp(): void
    {
        $this->translator = new SelectTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet(): void
    {
        $traits = class_uses($this->translator);

        self::assertContains(HasConditionsTrait::class, $traits);
        self::assertContains(SupportsJoinTrait::class, $traits);
        self::assertContains(SupportsForceIndexTrait::class, $traits);
        self::assertContains(SupportsWhereTrait::class, $traits);
        self::assertContains(SupportsGroupByTrait::class, $traits);
        self::assertContains(SupportsOrderByTrait::class, $traits);
        self::assertContains(SupportsLimitTrait::class, $traits);

        self::assertNotContains(SupportsValuesTrait::class, $traits);
    }

    /**
     * @dataProvider dpTranslatorTypes
     */
    public function testTranslatorAcceptsCorrectly(string $type, bool $accepts): void
    {
        $query = new Query($type, 'table', 't');

        self::assertSame($accepts, $this->translator->accepts($query));
    }

    public function dpTranslatorTypes(): array
    {
        return [
            [Query::TYPE_DELETE, false],
            [Query::TYPE_INSERT, false],
            [Query::TYPE_SELECT, true],
            [Query::TYPE_UPDATE, false],
        ];
    }

    public function testBasicQuery(): void
    {
        $query = Query::select('table');

        self::assertSame(
            "SELECT * FROM `table`",
            $this->translator->translate($query)
        );
    }

    public function testBasicQueryWithAlias(): void
    {
        $query = Query::select('table', 't');

        self::assertSame(
            "SELECT * FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumns(): void
    {
        $query = Query::select('table', 't');
        $query->setColumns('id', 'username');

        self::assertSame(
            "SELECT `t`.`id`, `t`.`username` FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithAliasForKey(): void
    {
        $query = Query::select('table', 't');
        $query->setColumns('id', 'u.username');

        self::assertSame(
            "SELECT `t`.`id`, `u`.`username` FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithSumEscapesOnlyColumns(): void
    {
        $query = Query::select('table', 't');
        $query->setColumns('SUM(amount)');

        self::assertSame(
            "SELECT SUM(`t`.`amount`) FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithSumNumericOrAsteriskIsNotEscaped(): void
    {
        $query = Query::select('table', 't');
        $query->setColumns('SUM(1)');

        self::assertSame(
            "SELECT SUM(1) FROM `table` `t`",
            $this->translator->translate($query)
        );

        $query->setColumns('SUM(*)');

        self::assertSame(
            "SELECT SUM(*) FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithSumThrowsOnNoValue(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Function SUM requires a value to be passed.');

        $query = Query::select('table', 't');
        $query->setColumns('SUM()');

        $this->translator->translate($query);
    }

    public function testLimitByItself(): void
    {
        $query = Query::select('table', 't');

        $query->limit(10);

        self::assertSame(
            "SELECT * FROM `table` `t` LIMIT 10",
            $this->translator->translate($query)
        );
    }

    public function testLimitAndOffset(): void
    {
        $query = Query::select('table', 't');

        $query->limit(10, 5);

        self::assertSame(
            "SELECT * FROM `table` `t` LIMIT 10,5",
            $this->translator->translate($query)
        );
    }

    public function testLimitAndOffsetSetTo0MeansNoLimitAndOffset(): void
    {
        $query = Query::select('table', 't');

        self::assertSame(
            "SELECT * FROM `table` `t`",
            $this->translator->translate($query)
        );

        $query->limit(10, 5);

        self::assertSame(
            "SELECT * FROM `table` `t` LIMIT 10,5",
            $this->translator->translate($query)
        );

        $query->limit(0, 0);

        self::assertSame(
            "SELECT * FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testLimitRequiredForOffset(): void
    {
        $query = Query::select('table', 't');

        $query->limit(0, 15);

        self::assertSame(
            "SELECT * FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testForceIndexWithRegularKey(): void
    {
        $query = Query::select('table', 't');

        $query->forceIndex('username');

        self::assertSame(
            "SELECT * FROM `table` `t` FORCE INDEX ('username')",
            $this->translator->translate($query)
        );
    }

    public function testForceIndexWithPrimaryKeyAsStringStillQuotes(): void
    {
        $query = Query::select('table', 't');

        $query->forceIndex('PRIMARY');

        self::assertSame(
            "SELECT * FROM `table` `t` FORCE INDEX ('PRIMARY')",
            $this->translator->translate($query)
        );
    }

    public function testForceIndexWithPrimaryKeyFromConstantDoesNotQuote(): void
    {
        $query = Query::select('table', 't');

        $query->forceIndex(Query::PRIMARY_KEY_INDEX);

        self::assertSame(
            "SELECT * FROM `table` `t` FORCE INDEX (PRIMARY)",
            $this->translator->translate($query)
        );
    }

    public function testGroupBy(): void
    {
        $query = Query::select('table', 't');

        $query->groupBy('username', 'u.email');

        self::assertSame(
            "SELECT * FROM `table` `t` GROUP BY `t`.`username`, `u`.`email`",
            $this->translator->translate($query)
        );
    }

    public function testOrderBy(): void
    {
        $query = Query::select('table', 't');
        $query->orderBy(OrderBy::asc('username'));

        self::assertSame(
            "SELECT * FROM `table` `t` ORDER BY `t`.`username` ASC",
            $this->translator->translate($query)
        );

        $query = Query::select('table', 't');
        $query->orderBy(OrderBy::desc('username'));

        self::assertSame(
            "SELECT * FROM `table` `t` ORDER BY `t`.`username` DESC",
            $this->translator->translate($query)
        );
    }

    public function testMultipleOrderBys(): void
    {
        $query = Query::select('table', 't');
        $query->orderBy(OrderBy::desc('username'));
        $query->orderBy(OrderBy::asc('u.test'));

        self::assertSame(
            "SELECT * FROM `table` `t` ORDER BY `t`.`username` DESC, `u`.`test` ASC",
            $this->translator->translate($query)
        );
    }

    public function testMultipleOrderByKeys(): void
    {
        $query = Query::select('table', 't');
        $query->orderBy(OrderBy::asc('username', 'email', 'updated_at'));

        self::assertSame(
            "SELECT * FROM `table` `t` ORDER BY `t`.`username` ASC, `t`.`email` ASC, `t`.`updated_at` ASC",
            $this->translator->translate($query)
        );
    }

    public function testMultipleOrderByWithSameKeysIsFineIfDirectionIsSame(): void
    {
        $query = Query::select('table', 't');
        $query->orderBy(OrderBy::asc('username', 'username', 'username'));
        $query->orderBy(OrderBy::asc('username'));

        self::assertSame(
            "SELECT * FROM `table` `t` ORDER BY `t`.`username` ASC",
            $this->translator->translate($query)
        );
    }

    public function testMultipleOrderByWithSameKeysThrowsOnDifferentDirection(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Cannot define order by key twice with different directions.');

        $query = Query::select('table', 't');
        $query->orderBy(OrderBy::asc('username'));
        $query->orderBy(OrderBy::desc('username'));

        $this->translator->translate($query);
    }

    public function testInnerJoin(): void
    {
        $query = Query::select('table', 't');

        $join = (new Join('join_table', 'jt'))
            ->onKey('id', '=', 'table_id');

        $query->innerJoin($join);

        self::assertSame(
            "SELECT * FROM `table` `t` INNER JOIN `join_table` `jt` ON (`t`.`id` = `jt`.`table_id`)",
            $this->translator->translate($query)
        );
    }

    public function testLeftJoin(): void
    {
        $query = Query::select('table', 't');

        $join = (new Join('join_table', 'jt'))
            ->onKey('id', '=', 'table_id');

        $query->leftJoin($join);

        self::assertSame(
            "SELECT * FROM `table` `t` LEFT JOIN `join_table` `jt` ON (`t`.`id` = `jt`.`table_id`)",
            $this->translator->translate($query)
        );
    }

    public function testJoinWithoutConditionsBuildsNoJoin(): void
    {
        $query = Query::select('table', 't');

        $join = new Join('join_table', 'jt');

        $query->leftJoin($join);

        self::assertSame(
            "SELECT * FROM `table` `t`",
            $this->translator->translate($query)
        );
    }

    public function testWhereValueCondition(): void
    {
        $query = Query::select('table', 't');
        $query->where('username', '=', 'amy');

        self::assertSame(
            "SELECT * FROM `table` `t` WHERE `t`.`username` = 'amy'",
            $this->translator->translate($query)
        );
    }

    public function testWhereValueConditionWithArrayValueGetsImploded(): void
    {
        $query = Query::select('table', 't');
        $query->where('username', 'IN', ['amy', 'tom']);

        self::assertSame(
            "SELECT * FROM `table` `t` WHERE `t`.`username` IN ('amy','tom')",
            $this->translator->translate($query)
        );
    }

    public function testWhereCallable(): void
    {
        $query = Query::select('table', 't');
        $query->whereCallable(static function(Query $query) {
            $query->where('username', '=', 'amy');
            $query->orWhere('username', '=', 'john');
        });

        self::assertSame(
            "SELECT * FROM `table` `t` WHERE (`t`.`username` = 'amy' OR `t`.`username` = 'john')",
            $this->translator->translate($query)
        );
    }

    public function testWhereCallableNested(): void
    {
        $query = Query::select('table', 't');
        $query->whereCallable(static function(Query $query) {
            $query->where('username', '=', 'amy');
            $query->orWhereCallable(static function(Query $query) {
                $query->where('test', '=', 'why');
            });
        });

        self::assertSame(
            "SELECT * FROM `table` `t` WHERE (`t`.`username` = 'amy' OR (`t`.`test` = 'why'))",
            $this->translator->translate($query)
        );
    }

    public function testWhereCallableNestedBreaksAfter5levels(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Recursion of callable WHERE clauses is too deep.');

        $query = Query::select('table', 't');

        $query->whereCallable(static function(Query $query) {
            $query->orWhereCallable(static function(Query $query) {
                $query->orWhereCallable(static function(Query $query) {
                    $query->orWhereCallable(static function(Query $query) {
                        $query->orWhereCallable(static function(Query $query) {
                            $query->where('test', '=', 'why');
                        });
                    });
                });
            });
        });

        $this->translator->translate($query);
    }

public function testEverySingleTraitOnSelectQueryTranslator(): void
{
    $query = Query::select('users', 'u');

    $query->setColumns('id','lastname','firstname','p.id','p.website');
    $query->forceIndex(Query::PRIMARY_KEY_INDEX);
    $query->where('lastname', '=', 'McTest');
    $query->whereCallable(static function(Query $query) {
        $query->where('firstname', '=', 'John');
        $query->orWhere('firstname', '=', 'Amy');
    });

    $join = new Join('profile', 'p');
    $join->onKey('id', '=', 'user_id');
    $join->onNull('updated_at');

    $query->leftJoin($join);

    $query->groupBy('lastname');
    $query->orderBy(OrderBy::desc('id'));
    $query->orderBy(OrderBy::asc('p.id', 'lastname'));

    $query->limit(50, 10);

    self::assertTrue($this->translator->accepts($query));
    self::assertSame(
        "SELECT `u`.`id`, `u`.`lastname`, `u`.`firstname`, `p`.`id`, `p`.`website` FROM `users` `u` FORCE INDEX (PRIMARY) LEFT JOIN `profile` `p` ON (`u`.`id` = `p`.`user_id` AND `u`.`updated_at` IS NULL) WHERE `u`.`lastname` = 'McTest' AND (`u`.`firstname` = 'John' OR `u`.`firstname` = 'Amy') GROUP BY `u`.`lastname` ORDER BY `u`.`id` DESC, `p`.`id` ASC, `u`.`lastname` ASC LIMIT 50,10",
        $this->translator->translate($query)
    );
}

    public function testStringableThingsAreActuallyStringified(): void
    {
        $stringable = new class {
            public function __toString(): string
            {
                return 'john';
            }
        };

        $query = Query::select('users', 'u');

        $query->setColumns('lastname');
        $query->where('lastname', '=', $stringable);

        self::assertTrue($this->translator->accepts($query));
        self::assertSame(
            "SELECT `u`.`lastname` FROM `users` `u` WHERE `u`.`lastname` = 'john'",
            $this->translator->translate($query)
        );
    }
}
