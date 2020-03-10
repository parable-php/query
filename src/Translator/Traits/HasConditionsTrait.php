<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Condition\CallableCondition;
use Parable\Query\Condition\ValueCondition;
use Parable\Query\Exception;
use Parable\Query\Query;
use Parable\Query\StringBuilder;

trait HasConditionsTrait
{
    /**
     * @param ValueCondition[]|CallableCondition[] $conditions
     *
     * @return StringBuilder
     * @throws Exception
     */
    protected function buildConditions(Query $query, array $conditions, $recursion = 0): StringBuilder
    {
        if ($recursion >= 5) {
            throw new Exception('Recursion of callable WHERE clauses is too deep.');
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
                StringBuilder::fromArray($this->quoteValuesFromArray($condition->getValue()), ',')->toString()
            );
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
