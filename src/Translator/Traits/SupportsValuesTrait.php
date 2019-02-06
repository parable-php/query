<?php

namespace Parable\Query\Translator\Traits;

use Parable\Query\Exception;
use Parable\Query\Query;
use Parable\Query\ValueSet;

trait SupportsValuesTrait
{
    protected function buildValues(Query $query): string
    {
        if ($query->getType() === Query::TYPE_UPDATE) {
            return $this->buildUpdateVales($query);
        } elseif ($query->getType() === Query::TYPE_INSERT) {
            return $this->buildInsertValues($query);
        }

        throw new Exception('Query type ' . $query->getType() . ' does not support values.');
    }

    protected function buildUpdateVales(Query $query): string
    {
        $valueSets = $query->getValueSets();

        if (count($valueSets) !== 1) {
            throw new Exception(sprintf(
                'Update queries must contain exactly one value set, %d provided.',
                count($valueSets)
            ));
        }

        $valueSet = reset($valueSets);

        $parts = [];

        foreach ($valueSet->getValues() as $key => $value) {
            $parts[] = sprintf(
                '%s = %s',
                $this->quoteIdentifier($key),
                $this->quote($value)
            );
        }

        return 'SET ' . implode(', ', $parts);
    }

    protected function buildInsertValues(Query $query): string
    {
        $valueSets = $query->getValueSets();

        if (count($valueSets) === 0) {
            throw new Exception('Insert queries must contain at least one value set.');
        }

        $keys = $this->getKeysFromValueSets($valueSets);

        $valueParts = [];

        foreach ($valueSets as $valueSet) {
            $valueParts[] = '(' . implode(', ', $this->quoteValuesFromArray($valueSet->getValues())) . ')';
        }

        return sprintf(
            '(%s) VALUES %s',
            implode(',', $this->quoteIdentifiersFromArray($keys)),
            implode(',', $valueParts)
        );
    }

    /**
     * @param ValueSet[] $valueSets
     */
    protected function getKeysFromValueSets(array $valueSets): array
    {
        $keys = [];

        foreach ($valueSets as $valueSet) {
            if ($keys !== [] && $valueSet->getKeys() !== $keys) {
                throw new Exception('Not all value sets match on keys: ' . implode(', ', $keys));
            }

            $keys = $valueSet->getKeys();
        }

        return $keys;
    }
}
