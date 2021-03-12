<?php declare(strict_types=1);

namespace Parable\Query\Translators;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translators\Traits\HasConditionsTrait;
use Parable\Query\Translators\Traits\SupportsForceIndexTrait;
use Parable\Query\Translators\Traits\SupportsGroupByTrait;
use Parable\Query\Translators\Traits\SupportsJoinTrait;
use Parable\Query\Translators\Traits\SupportsLimitTrait;
use Parable\Query\Translators\Traits\SupportsOrderByTrait;
use Parable\Query\Translators\Traits\SupportsWhereTrait;
use Parable\Query\Translators\TranslatorInterface;

class SelectTranslator extends AbstractTranslator implements TranslatorInterface
{
    use HasConditionsTrait;
    use SupportsForceIndexTrait;
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
        $queryParts = new StringBuilder();

        $queryParts->add('SELECT');

        if (!empty($query->getColumns())) {
            $quotedColumns = $this->quotePrefixedIdentifiersFromArray($query, $query->getColumns());

            $queryParts->add((string)StringBuilder::fromArray($quotedColumns, ', '));
        } else {
            $queryParts->add('*');
        }

        $queryParts->add('FROM', $this->quoteIdentifier($query->getTableName()));

        if ($query->getTableAlias() !== null) {
            $queryParts->add($this->quoteIdentifier($query->getTableAlias()));
        }

        $queryParts->add(
            $this->buildForceIndex($query),
            $this->buildJoins($query),
            $this->buildWhere($query),
            $this->buildGroupBy($query),
            $this->buildOrderBy($query),
            $this->buildLimit($query)
        );

        return (string)$queryParts;
    }
}
