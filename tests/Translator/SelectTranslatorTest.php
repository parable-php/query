<?php declare(strict_types=1);

namespace Parable\Query\Tests\Translator;

use Parable\Query\Exception;
use Parable\Query\Join;
use Parable\Query\Query;
use Parable\Query\Translator\SelectTranslator;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use PDO;

class SelectTranslatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SelectTranslator
     */
    protected $translator;

    public function setUp()
    {
        $this->translator = new SelectTranslator(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testAppropriateTraitsSet()
    {
        $traits = class_uses($this->translator);

        self::assertContains(HasConditionsTrait::class, $traits);
        self::assertContains(SupportsJoinTrait::class, $traits);
        self::assertContains(SupportsWhereTrait::class, $traits);
        self::assertContains(SupportsGroupByTrait::class, $traits);
        self::assertContains(SupportsOrderByTrait::class, $traits);
        self::assertContains(SupportsLimitTrait::class, $traits);

        self::assertNotContains(SupportsValuesTrait::class, $traits);
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
            [Query::TYPE_SELECT, true],
            [Query::TYPE_UPDATE, false],
        ];
    }

    public function testBasicQuery()
    {
        $query = Query::select('table');

        self::assertSame(
            "SELECT * FROM `table`",
            $this->translator->translate($query)
        );
    }

    public function testBasicQueryWithAlias()
    {
        $query = Query::select('table', 't');

        self::assertSame(
            "SELECT * FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumns()
    {
        $query = Query::select('table', 't');
        $query->setColumns(['id', 'username']);

        self::assertSame(
            "SELECT `t`.`id`, `t`.`username` FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithAliasForKey()
    {
        $query = Query::select('table', 't');
        $query->setColumns(['id', 'u.username']);

        self::assertSame(
            "SELECT `t`.`id`, `u`.`username` FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithSumEscapesOnlyColumns()
    {
        $query = Query::select('table', 't');
        $query->setColumns(['SUM(amount)']);

        self::assertSame(
            "SELECT SUM(`t`.`amount`) FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithSumNumericOrAsteriskIsNotEscaped()
    {
        $query = Query::select('table', 't');
        $query->setColumns(['SUM(1)']);

        self::assertSame(
            "SELECT SUM(1) FROM `table` AS `t`",
            $this->translator->translate($query)
        );

        $query->setColumns(['SUM(*)']);

        self::assertSame(
            "SELECT SUM(*) FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testSetColumnsWithSumThrowsOnNoValue()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Function SUM requires a value to be passed.');

        $query = Query::select('table', 't');
        $query->setColumns(['SUM()']);

        $this->translator->translate($query);
    }

    public function testLimitAndOffset()
    {
        $query = Query::select('table', 't');

        $query->limit(10, 5);

        self::assertSame(
            "SELECT * FROM `table` AS `t` LIMIT 10,5",
            $this->translator->translate($query)
        );
    }

    public function testLimitAndOffsetSetTo0MeansNoLimitAndOffset()
    {
        $query = Query::select('table', 't');

        self::assertSame(
            "SELECT * FROM `table` AS `t`",
            $this->translator->translate($query)
        );

        $query->limit(10, 5);

        self::assertSame(
            "SELECT * FROM `table` AS `t` LIMIT 10,5",
            $this->translator->translate($query)
        );

        $query->limit(0, 0);

        self::assertSame(
            "SELECT * FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testLimitRequiredForOffset()
    {
        $query = Query::select('table', 't');

        $query->limit(0, 15);

        self::assertSame(
            "SELECT * FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testGroupBy()
    {
        $query = Query::select('table', 't');

        $query->groupBy(['username', 'u.email']);

        self::assertSame(
            "SELECT * FROM `table` AS `t` GROUP BY `t`.`username`, `u`.`email`",
            $this->translator->translate($query)
        );
    }

    public function testOrderBy()
    {
        $query = Query::select('table', 't');
        $query->orderBy('username');

        self::assertSame(
            "SELECT * FROM `table` AS `t` ORDER BY `t`.`username` ASC",
            $this->translator->translate($query)
        );

        $query = Query::select('table', 't');
        $query->orderBy('username', Query::ORDER_ASC);

        self::assertSame(
            "SELECT * FROM `table` AS `t` ORDER BY `t`.`username` ASC",
            $this->translator->translate($query)
        );

        $query = Query::select('table', 't');
        $query->orderBy('username', Query::ORDER_DESC);

        self::assertSame(
            "SELECT * FROM `table` AS `t` ORDER BY `t`.`username` DESC",
            $this->translator->translate($query)
        );
    }

    public function testMultipleOrderBys()
    {
        $query = Query::select('table', 't');
        $query->orderBy('username', Query::ORDER_DESC);
        $query->orderBy('u.test', Query::ORDER_ASC);

        self::assertSame(
            "SELECT * FROM `table` AS `t` ORDER BY `t`.`username` DESC, `u`.`test` ASC",
            $this->translator->translate($query)
        );
    }

    public function testOrderByThrowsOnInvalidDirection()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Order direction NOPE is invalid.');

        $query = Query::select('table', 't');
        $query->orderBy('username', 'NOPE');

        $this->translator->translate($query);
    }

    public function testInnerJoin()
    {
        $query = Query::select('table', 't');

        $join = (new Join('join_table', 'jt'))
            ->onKey('id', '=', 'table_id');

        $query->innerJoin($join);

        self::assertSame(
            "SELECT * FROM `table` AS `t` INNER JOIN `join_table` AS `jt` ON (`t`.`id` = `jt`.`table_id`)",
            $this->translator->translate($query)
        );
    }

    public function testLeftJoin()
    {
        $query = Query::select('table', 't');

        $join = (new Join('join_table', 'jt'))
            ->onKey('id', '=', 'table_id');

        $query->leftJoin($join);

        self::assertSame(
            "SELECT * FROM `table` AS `t` LEFT JOIN `join_table` AS `jt` ON (`t`.`id` = `jt`.`table_id`)",
            $this->translator->translate($query)
        );
    }

    public function testJoinWithoutConditionsBuildsNoJoin()
    {
        $query = Query::select('table', 't');

        $join = new Join('join_table', 'jt');

        $query->leftJoin($join);

        self::assertSame(
            "SELECT * FROM `table` AS `t`",
            $this->translator->translate($query)
        );
    }

    public function testWhereValueCondition()
    {
        $query = Query::select('table', 't');
        $query->where('username', '=', 'amy');

        self::assertSame(
            "SELECT * FROM `table` AS `t` WHERE `t`.`username` = 'amy'",
            $this->translator->translate($query)
        );
    }

    public function testWhereValueConditionWithArrayValueGetsImploded()
    {
        $query = Query::select('table', 't');
        $query->where('username', 'IN', ['amy', 'tom']);

        self::assertSame(
            "SELECT * FROM `table` AS `t` WHERE `t`.`username` IN ('amy','tom')",
            $this->translator->translate($query)
        );
    }

    public function testWhereCallable()
    {
        $query = Query::select('table', 't');
        $query->whereCallable(function(Query $query) {
            $query->where('username', '=', 'amy');
            $query->orWhere('username', '=', 'john');
        });

        self::assertSame(
            "SELECT * FROM `table` AS `t` WHERE (`t`.`username` = 'amy' OR `t`.`username` = 'john')",
            $this->translator->translate($query)
        );
    }

    public function testWhereCallableNested()
    {
        $query = Query::select('table', 't');
        $query->whereCallable(function(Query $query) {
            $query->where('username', '=', 'amy');
            $query->orWhereCallable(function(Query $query) {
                $query->where('test', '=', 'why');
            });
        });

        self::assertSame(
            "SELECT * FROM `table` AS `t` WHERE (`t`.`username` = 'amy' OR (`t`.`test` = 'why'))",
            $this->translator->translate($query)
        );
    }

    public function testWhereCallableNestedBreaksAfter5levels()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Recursion of callable WHERE clauses is too deep.');

        $query = Query::select('table', 't');

        $query->whereCallable(function(Query $query) {
            $query->orWhereCallable(function(Query $query) {
                $query->orWhereCallable(function(Query $query) {
                    $query->orWhereCallable(function(Query $query) {
                        $query->orWhereCallable(function(Query $query) {
                            $query->where('test', '=', 'why');
                        });
                    });
                });
            });
        });

        $this->translator->translate($query);
    }

    public function testEverySingleTraitOnSelectQueryTranslator()
    {
        /*
         *
        self::assertContains(HasConditionsTrait::class, $traits);
        self::assertContains(SupportsJoinTrait::class, $traits);
        self::assertContains(SupportsWhereTrait::class, $traits);
        self::assertContains(SupportsGroupByTrait::class, $traits);
        self::assertContains(SupportsOrderByTrait::class, $traits);
        self::assertContains(SupportsLimitTrait::class, $traits);
         */

        $query = Query::select('users', 'u');

        $query->setColumns(['id','lastname','firstname','p.id','p.website']);
        $query->where('lastname', '=', 'McTest');
        $query->whereCallable(function(Query $query) {
            $query->where('firstname', '=', 'John');
            $query->orWhere('firstname', '=', 'Amy');
        });

        $join = new Join('profile', 'p');
        $join->onKey('id', '=', 'user_id');
        $join->onNull('updated_at');

        $query->leftJoin($join);

        $query->groupBy(['lastname']);
        $query->orderBy('id', Query::ORDER_DESC);

        $query->limit(50, 10);

        self::assertTrue($this->translator->accepts($query));
        self::assertSame(
            "SELECT `u`.`id`, `u`.`lastname`, `u`.`firstname`, `p`.`id`, `p`.`website` FROM `users` AS `u` LEFT JOIN `profile` AS `p` ON (`u`.`id` = `p`.`user_id` AND `u`.`updated_at` IS NULL) WHERE `u`.`lastname` = 'McTest' AND (`u`.`firstname` = 'John' OR `u`.`firstname` = 'Amy') GROUP BY `u`.`lastname` ORDER BY `u`.`id` DESC LIMIT 50,10",
            $this->translator->translate($query)
        );
    }
}
