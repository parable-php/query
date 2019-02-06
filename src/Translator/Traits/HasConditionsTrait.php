<?php

namespace Parable\Query\Translator\Traits;

use Parable\Query\Condition\AbstractCondition;
use Parable\Query\Condition\ValueCondition;
use Parable\Query\Condition\CallableCondition;
use Parable\Query\Exception;
use Parable\Query\Query;

trait HasConditionsTrait
{
    /**
     * @param ValueCondition[]|CallableCondition[] $conditions
     *
     * @return string[]
     */
    protected function buildConditions(Query $query, array $conditions, $recursion = 0): array
    {
        if ($recursion >= 5) {
            throw new Exception('Recursion of callable WHERE clauses is too deep.');
        }

        $parts = [];

        foreach ($conditions as $index => $condition) {
            if ($condition instanceof CallableCondition) {
                $part = $this->handleCallableCondition($query, $condition, $recursion);

                if ($index > 0) {
                    $part = $condition->getType() . ' ' . $part;
                }

                $parts[] = $part;
                continue;
            }

            // By default the value is simply the value
            $value = $condition->getValue();

            $string = $this->quoteValueIfNeeded($query, $condition, $value);

            $part = trim($string);

            if ($index > 0) {
                $part = $condition->getType() . ' ' . $part;
            }

            $parts[] = $part;
        }

        return $parts;
    }

    protected function handleCallableCondition(Query $query, CallableCondition $condition, int $recursion): string
    {
        $subQuery = new Query($query->getType(), $query->getTableName(), $query->getTableAlias());

        $callable = $condition->getCallable();
        $callable($subQuery);

        $part = '(' . $this->buildWhere($subQuery, $recursion + 1) . ')';

        return $part;
    }

    protected function quoteValueIfNeeded(Query $query, ValueCondition $condition, $value): string
    {
        if ($condition->isValueKey()) {
            $value = sprintf(
                '%s.%s',
                $this->quoteIdentifier($condition->getTableName()),
                $this->quoteIdentifier($condition->getValue())
            );
        } elseif (is_array($condition->getValue())) {
            $value = '(' . implode(',', $this->quoteValuesFromArray($condition->getValue())) . ')';
        } elseif (is_string($condition->getValue())) {
            $value = $this->quote($condition->getValue());
        }

        return sprintf(
            '%s %s %s',
            $this->quoteIdentifierPrefixedKey($query->getTableAliasOrName(), $condition->getKey()),
            $condition->getComparator(),
            $value
        );
    }
}
