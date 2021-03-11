<?php

namespace Parable\Query\Translator\Traits;

use Parable\Query\QueryException;
use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\ValueSet;

trait SupportsValuesTrait
{
    protected function buildValues(Query $query): string
    {
        if ($query->getType() === Query::TYPE_UPDATE) {
            return $this->buildUpdateVales($query);
        }

        if ($query->getType() === Query::TYPE_INSERT) {
            return $this->buildInsertValues($query);
        }

        throw new QueryException('Query type ' . $query->getType() . ' does not support values.');
    }

    protected function buildUpdateVales(Query $query): string
    {
        $valueSets = $query->getValueSets();

        if ($query->countValueSets() !== 1) {
            throw new QueryException(sprintf(
                'Update queries must contain exactly one value set, %d provided.',
                count($valueSets)
            ));
        }

        $valueSet = reset($valueSets);

        $parts = new StringBuilder(', ');

        foreach ($valueSet->getValues() as $key => $value) {
            if ($value === null) {
                $cleanValue = 'NULL';
            } else {
                $cleanValue = $this->quote($value);
            }

            $parts->add(sprintf(
                '%s = %s',
                $this->quoteIdentifier($key),
                $cleanValue
            ));
        }

        return 'SET ' . (string)$parts;
    }

    protected function buildInsertValues(Query $query): string
    {
        if ($query->countValueSets() < 1) {
            throw new QueryException('Insert queries must contain at least one value set.');
        }

        $valueParts = new StringBuilder(', ');

        foreach ($query->getValueSets() as $valueSet) {
            $valueSetParts = StringBuilder::fromArray($this->quoteValuesFromArray($valueSet->getValues()), ', ');
            $valueParts->add(sprintf(
                '(%s)',
                (string)$valueSetParts
            ));
        }

        $keyParts = StringBuilder::fromArray(
            $this->quoteIdentifiersFromArray(
                $this->getKeysFromValueSets($query->getValueSets())
            ),
            ', '
        );

        return sprintf(
            '(%s) VALUES %s',
            (string)$keyParts,
            (string)$valueParts
        );
    }

    /**
     * @param ValueSet[] $valueSets
     *
     * @return string[]
     * @throws QueryException
     */
    protected function getKeysFromValueSets(array $valueSets): array
    {
        $keys = [];

        foreach ($valueSets as $valueSet) {
            if ($keys !== [] && $valueSet->getKeys() !== $keys) {
                throw new QueryException('Not all value sets match on keys: ' . implode(', ', $keys));
            }

            $keys = $valueSet->getKeys();
        }

        return $keys;
    }
}
