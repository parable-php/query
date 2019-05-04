<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Query;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\TranslatorInterface;

class SelectTranslator extends AbstractTranslator implements TranslatorInterface
{
    use HasConditionsTrait;
    use SupportsJoinTrait;
    use SupportsWhereTrait;
    use SupportsGroupByTrait;
    use SupportsOrderByTrait;
    use SupportsLimitTrait;

    public function accepts(Query $query): bool
    {
        return $query->getType() === Query::TYPE_SELECT;
    }

    public function translate(Query $query): string
    {
        $parts = [];

        $parts[] = 'SELECT';

        if (!empty($query->getColumns())) {
            $parts[] = implode(', ', $this->quotePrefixedIdentifiersFromArray($query, $query->getColumns()));
        } else {
            $parts[] = '*';
        }

        $parts[] = 'FROM';
        $parts[] = $this->quoteIdentifier($query->getTableName());

        if ($query->getTableAlias() !== null) {
            $parts[] = 'AS ' . $this->quoteIdentifier($query->getTableAlias());
        }

        $parts[] = $this->buildJoins($query);
        $parts[] = $this->buildWhere($query);
        $parts[] = $this->buildGroupBy($query);
        $parts[] = $this->buildOrderBy($query);
        $parts[] = $this->buildLimit($query);

        return trim(implode(' ', array_filter($parts)));
    }
}
