<?php declare(strict_types=1);

namespace Parable\Query\Translators\Traits;

use Parable\Query\Query;
use Parable\Query\StringBuilder;

trait SupportsJoinTrait
{
    protected function buildJoins(Query $query): string
    {
        if (!$query->hasJoins()) {
            return '';
        }

        $joinParts = new StringBuilder();

        $joinParts->merge($this->buildJoinsFromType($query, Query::JOIN_TYPE_INNER));
        $joinParts->merge($this->buildJoinsFromType($query, Query::JOIN_TYPE_LEFT));

        return (string)$joinParts;
    }

    protected function buildJoinsFromType(Query $query, string $type): StringBuilder
    {
        $joins = $query->getJoinsByType($type);

        $joinParts = new StringBuilder();

        foreach ($joins as $join) {
            $conditionParts = $this->buildConditions($query, $join->getOnConditions());

            if ($conditionParts->isEmpty()) {
                continue;
            }

            $tableParts = new StringBuilder();
            $tableParts->add($this->quoteIdentifier($join->getTableName()));

            if ($join->getTableAlias() !== null) {
                $tableParts->add($this->quoteIdentifier($join->getTableAlias()));
            }

            $joinParts->add(sprintf(
                '%s JOIN %s ON (%s)',
                $type,
                (string)$tableParts,
                (string)$conditionParts
            ));
        }

        return $joinParts;
    }
}
