<?php

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;

trait SupportsJoinTrait
{
    protected function buildJoins(Query $query): string
    {
        if (!$query->hasJoins()) {
            return '';
        }

        $innerJoins = $this->buildJoinsFromType($query, Query::JOIN_TYPE_INNER);
        $leftJoins  = $this->buildJoinsFromType($query, Query::JOIN_TYPE_LEFT);

        $allJoins = array_merge($leftJoins, $innerJoins);

        return implode(' ', $allJoins);
    }

    protected function buildJoinsFromType(Query $query, string $type): array
    {
        $joins = $query->getJoinsByType($type);

        $joinStrings = [];

        foreach ($joins as $join) {
            $conditions = $this->buildConditions($query, $join->getOnConditions());

            if (empty($conditions)) {
                continue;
            }

            $string = implode(' ', $conditions);

            $tableName = $this->quoteIdentifier($join->getTableName());

            if ($join->getTableAlias() !== null) {
                $tableName .= ' AS ' . $this->quoteIdentifier($join->getTableAlias());
            }

            $joinClause = sprintf(
                '%s JOIN %s ON (%s)',
                $type,
                $tableName,
                $string
            );

            $joinStrings[] = $joinClause;
        }

        return $joinStrings;
    }
}
