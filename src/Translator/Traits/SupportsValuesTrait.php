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

        if ($query->countValueSets() !== 1) {
            throw new Exception(sprintf(
                'Update queries must contain exactly one value set, %d provided.',
                count($valueSets)
            ));
        }

        $valueSet = reset($valueSets);

        $parts = [];

        foreach ($valueSet->getValues() as $key => $value) {
            if ($value === null) {
                $cleanValue = 'NULL';
            } else {
                $cleanValue = $this->quote($value);
            }

            $parts[] = sprintf(
                '%s = %s',
                $this->quoteIdentifier($key),
                $cleanValue
            );
        }

        return 'SET ' . implode(', ', $parts);
    }

    protected function buildInsertValues(Query $query): string
    {
        if ($query->countValueSets() < 1) {
            throw new Exception('Insert queries must contain at least one value set.');
        }

        $valueParts = [];

        foreach ($query->getValueSets() as $valueSet) {
            $valueParts[] = '(' . implode(', ', $this->quoteValuesFromArray($valueSet->getValues())) . ')';
        }

        return sprintf(
            '(%s) VALUES %s',
            implode(',', $this->quoteIdentifiersFromArray($this->getKeysFromValueSets($query->getValueSets()))),
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
