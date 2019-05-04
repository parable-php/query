<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Query;
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
        $parts = [
            'UPDATE',
            $this->quoteIdentifier($query->getTableName()),
        ];

        if ($query->getTableAlias() !== null) {
            $parts[] = $this->quoteIdentifier($query->getTableAlias());
        }

        $parts[] = $this->buildJoins($query);
        $parts[] = $this->buildValues($query);
        $parts[] = $this->buildWhere($query);

        return trim(implode(' ', array_filter($parts)));
    }
}
