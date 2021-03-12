<?php declare(strict_types=1);

namespace Parable\Query\Translators;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translators\Traits\HasConditionsTrait;
use Parable\Query\Translators\Traits\SupportsJoinTrait;
use Parable\Query\Translators\Traits\SupportsValuesTrait;
use Parable\Query\Translators\Traits\SupportsWhereTrait;
use Parable\Query\Translators\TranslatorInterface;

class UpdateTranslator extends AbstractTranslator implements TranslatorInterface
{
    use HasConditionsTrait;
    use SupportsJoinTrait;
    use SupportsValuesTrait;
    use SupportsWhereTrait;

    public function accepts(Query $query): bool
    {
        return $query->getType() === Query::TYPE_UPDATE;
    }

    public function translate(Query $query): string
    {
        $queryParts = new StringBuilder();

        $queryParts->add(
            'UPDATE',
            $this->quoteIdentifier($query->getTableName())
        );

        $queryParts->add(
            $this->buildJoins($query),
            $this->buildValues($query),
            $this->buildWhere($query)
        );

        return (string)$queryParts;
    }
}
