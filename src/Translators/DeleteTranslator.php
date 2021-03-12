<?php declare(strict_types=1);

namespace Parable\Query\Translators;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translators\TranslatorInterface;
use Parable\Query\Translators\Traits\HasConditionsTrait;
use Parable\Query\Translators\Traits\SupportsGroupByTrait;
use Parable\Query\Translators\Traits\SupportsJoinTrait;
use Parable\Query\Translators\Traits\SupportsLimitTrait;
use Parable\Query\Translators\Traits\SupportsOrderByTrait;
use Parable\Query\Translators\Traits\SupportsWhereTrait;

class DeleteTranslator extends AbstractTranslator implements TranslatorInterface
{
    use HasConditionsTrait;
    use SupportsJoinTrait;
    use SupportsWhereTrait;
    use SupportsGroupByTrait;
    use SupportsOrderByTrait;
    use SupportsLimitTrait;

    public function accepts(Query $query): bool
    {
        return $query->getType() === Query::TYPE_DELETE;
    }

    public function translate(Query $query): string
    {
        $queryParts = new StringBuilder();

        $queryParts->add(
            'DELETE',
            'FROM',
            $this->quoteIdentifier($query->getTableName()),
            $this->buildJoins($query),
            $this->buildWhere($query),
            $this->buildOrderBy($query),
            $this->buildLimit($query)
        );

        return (string)$queryParts;
    }
}
