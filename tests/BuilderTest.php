<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Builder;
use Parable\Query\Query;
use Parable\Query\ValueSet;
use PDO;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    public function setUp()
    {
        $this->builder = new Builder(new PDO('sqlite::memory:'));

        parent::setUp();
    }

    public function testDeleteBasicQuery()
    {
        $query = Query::delete('table');

        self::assertSame(
            "DELETE FROM `table`",
            $this->builder->build($query)
        );
    }

    public function testDeleteBasicQueryWithAlias()
    {
        $query = Query::delete('table', 't');

        self::assertSame(
            "DELETE `t` FROM `table` AS `t`",
            $this->builder->build($query)
        );
    }

    public function testInsertBasicQuery()
    {
        $query = Query::insert('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "INSERT INTO `table` (`username`) VALUES ('test')",
            $this->builder->build($query)
        );
    }

    public function testSelectBasicQuery()
    {
        $query = Query::select('table');

        self::assertSame(
            "SELECT * FROM `table`",
            $this->builder->build($query)
        );
    }

    public function testSelectBasicQueryWithAlias()
    {
        $query = Query::select('table', 't');

        self::assertSame(
            "SELECT * FROM `table` AS `t`",
            $this->builder->build($query)
        );
    }

    public function testUpdateBasicQuery()
    {
        $query = Query::update('table');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "UPDATE `table` SET `username` = 'test'",
            $this->builder->build($query)
        );
    }

    public function testUpdateBasicQueryWithAlias()
    {
        $query = Query::update('table', 't');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "UPDATE `table` `t` SET `username` = 'test'",
            $this->builder->build($query)
        );
    }

    public function testNonsenseTypeQueryThrows()
    {
        $this->expectException(\Parable\Query\Exception::class);
        $this->expectExceptionMessage('Could not find suitable translater for query with type: NONSENSE');

        $this->builder->build(new Query('NONSENSE', 'table'));
    }
}
