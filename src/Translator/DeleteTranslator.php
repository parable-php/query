<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\TranslatorInterface;

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
