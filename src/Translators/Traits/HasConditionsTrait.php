<?php declare(strict_types=1);

namespace Parable\Query\Translators\Traits;

use Parable\Query\Conditions\CallableCondition;
use Parable\Query\Conditions\ValueCondition;
use Parable\Query\QueryException;
use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Stringable;

trait HasConditionsTrait
{
    /**
     * @param ValueCondition[]|CallableCondition[] $conditions
     *
     * @return StringBuilder
     * @throws QueryException
     */
    protected function buildConditions(Query $query, array $conditions, $recursion = 0): StringBuilder
    {
        if ($recursion >= 5) {
            throw new QueryException('Recursion of callable WHERE clauses is too deep.');
        }

        $conditionParts = new StringBuilder();

        foreach ($conditions as $index => $condition) {
            if ($condition instanceof CallableCondition) {
                $part = $this->handleCallableCondition($query, $condition, $recursion);

                if ($index > 0) {
                    $part = $condition->getType() . ' ' . $part;
                }

                $conditionParts->add($part);

                continue;
            }

            $part = $this->quoteValueIfNeeded($query, $condition, $condition->getValue());

            if ($index > 0) {
                $part = $condition->getType() . ' ' . trim($part);
            }

            $conditionParts->add($part);
        }

        return $conditionParts;
    }

    protected function handleCallableCondition(Query $query, CallableCondition $condition, int $recursion): string
    {
        $subQuery = $query->createCleanClone();
        $callable = $condition->getCallable();

        $callable($subQuery);

        return sprintf(
            '(%s)',
            $this->buildWhere($subQuery, $recursion + 1)
        );
    }

    protected function quoteValueIfNeeded(Query $query, ValueCondition $condition, $value): string
    {
        if ($condition->isValueKey()) {
            $value = sprintf(
                '%s.%s',
                $this->quoteIdentifier($condition->getTableName()),
                $this->quoteIdentifier((string)$condition->getValue())
            );
        } elseif (is_array($condition->getValue())) {
            $value = sprintf(
                '(%s)',
                (string)StringBuilder::fromArray($this->quoteValuesFromArray($condition->getValue()), ',')
            );
        } elseif ($this->isStringLike($condition->getValue())) {
            $value = $this->quote($condition->getValue());
        }

        return sprintf(
            '%s %s %s',
            $this->quoteIdentifierPrefixedKey($query->getTableAliasOrName(), $condition->getKey()),
            $condition->getComparator(),
            $value
        );
    }

    private function isStringLike($value): bool
    {
        return is_string($value)
            || $value instanceof Stringable;
    }
}
