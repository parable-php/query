<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translator\Traits\HasConditionsTrait;
use Parable\Query\Translator\Traits\SupportsForceIndexTrait;
use Parable\Query\Translator\Traits\SupportsGroupByTrait;
use Parable\Query\Translator\Traits\SupportsJoinTrait;
use Parable\Query\Translator\Traits\SupportsLimitTrait;
use Parable\Query\Translator\Traits\SupportsOrderByTrait;
use Parable\Query\Translator\Traits\SupportsWhereTrait;
use Parable\Query\TranslatorInterface;

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

            $queryParts->add(StringBuilder::fromArray($quotedColumns, ', ')->toString());
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

        return $queryParts->toString();
    }
}
