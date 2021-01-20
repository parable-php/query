<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Builder;
use Parable\Query\Exception;
use Parable\Query\Query;
use Parable\Query\Tests\Classes\NonsenseTranslator;
use Parable\Query\ValueSet;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    protected PDO $pdo;
    protected Builder $builder;

    public function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE `users` (
              `id` INTEGER PRIMARY KEY,
              `username` TEXT NOT NULL
            );
        ");

        $this->builder = new Builder($this->pdo);

        parent::setUp();
    }

    public function testDeleteBasicQuery(): void
    {
        $query = Query::delete('users');

        self::assertSame(
            "DELETE FROM `users`",
            $this->builder->build($query)
        );

        self::assertInstanceOf(PDOStatement::class, $this->pdo->query($this->builder->build($query)));
    }

    public function testInsertBasicQuery(): void
    {
        $query = Query::insert('users');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "INSERT INTO `users` (`username`) VALUES ('test')",
            $this->builder->build($query)
        );

        self::assertInstanceOf(PDOStatement::class, $this->pdo->query($this->builder->build($query)));
    }

    public function testSelectBasicQuery(): void
    {
        $query = Query::select('users');

        self::assertSame(
            "SELECT * FROM `users`",
            $this->builder->build($query)
        );

        self::assertInstanceOf(PDOStatement::class, $this->pdo->query($this->builder->build($query)));
    }

    public function testSelectBasicQueryWithAlias(): void
    {
        $query = Query::select('users', 'u');

        self::assertSame(
            "SELECT * FROM `users` `u`",
            $this->builder->build($query)
        );

        self::assertInstanceOf(PDOStatement::class, $this->pdo->query($this->builder->build($query)));
    }

    public function testUpdateBasicQuery(): void
    {
        $query = Query::update('users');
        $query->addValueSet(new ValueSet([
            'username' => 'test',
        ]));

        self::assertSame(
            "UPDATE `users` SET `username` = 'test'",
            $this->builder->build($query)
        );

        self::assertInstanceOf(PDOStatement::class, $this->pdo->query($this->builder->build($query)));
    }

    public function testNonsenseTypeQueryThrows(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find suitable translator for query with type: NONSENSE');

        $this->builder->build(new Query('NONSENSE', 'users'));
    }

    public function testBuilderCanHaveAddtionalTranslators(): void
    {
        $builder = new class ($this->pdo) extends Builder {
            protected function getTranslators(): array
            {
                return array_merge(
                    parent::getTranslators(),
                    [NonsenseTranslator::class]
                );
            }
        };

        self::assertSame('Nonsense query!', $builder->build(new Query('NONSENSE', 'users')));
    }
}
