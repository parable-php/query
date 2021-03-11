<?php declare(strict_types=1);

namespace Parable\Query\Tests;

use Parable\Query\Condition\CallableCondition;
use Parable\Query\QueryException;
use Parable\Query\Query;
use Parable\Query\Translator\SelectTranslator;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use PDO;
use PHPUnit\Framework\TestCase;

class ConditionsTest extends TestCase
{
    protected SelectTranslator $translator;

    public function testSetInvalidType(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Invalid where type provided: INVALID_TYPE');

        new CallableCondition(function () {
            return 'yay';
        }, 'INVALID_TYPE');
    }

    public function testSupportValuesTraitDoesNotTakeOtherTypeQueries(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Query type SELECT does not support values.');

        $this->translator = new class (new PDO('sqlite::memory:')) extends SelectTranslator {
            use SupportsValuesTrait;

            public function translate(Query $query): string
            {
                $this->buildValues($query);
                return parent::translate($query);
            }
        };

        $query = new class (Query::TYPE_SELECT, 'table', 't') extends Query {
        };

        $this->translator->translate($query);
    }
}
