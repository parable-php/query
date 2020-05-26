<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\TranslatorInterface;

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

        return $queryParts->toString();
    }
}
