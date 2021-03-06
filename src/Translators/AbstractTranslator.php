<?php declare(strict_types=1);

namespace Parable\Query\Translators;

use Parable\Query\QueryException;
use Parable\Query\Query;
use Parable\Query\StringBuilder;
use PDO;

abstract class AbstractTranslator
{
    public function __construct(
        protected PDO $connection
    ) {}

    protected function quote($value): string
    {
        return $this->connection->quote((string)$value);
    }

    protected function quoteIdentifier(string $string): string
    {
        return sprintf(
            '`%s`',
            trim($string, '`')
        );
    }

    protected function quoteIdentifierPrefixedKey(string $tableName, string $key): string
    {
        return sprintf(
            '%s.%s',
            $this->quoteIdentifier($tableName),
            $this->quoteIdentifier($key)
        );
    }

    protected function quoteValuesFromArray(array $array): array
    {
        $quoted = [];

        foreach ($array as $key => $value) {
            $quoted[$key] = $this->quote($value);
        }

        return $quoted;
    }

    protected function quoteIdentifiersFromArray(array $array): array
    {
        $quoted = [];

        foreach ($array as $key) {
            $quoted[] = $this->quoteIdentifier($key);
        }

        return $quoted;
    }

    protected function quotePrefixedIdentifiersFromArray(Query $query, array $array): array
    {
        $quoted = [];

        foreach ($array as $key => $value) {
            if ($value === '*' || ctype_digit($value)) {
                $quoted[$key] = $value;
            } elseif ($this->extractSqlFunction($value) !== null) {
                $quoted[$key] = $this->quoteIdentifiersFromSqlFunctionString($query, $value);
            } elseif (str_contains($value, '.')) {
                [$tableName, $tableKey] = explode('.', $value);
                $quoted[$key] = $this->quoteIdentifierPrefixedKey($tableName, $tableKey);
            } else {
                $quoted[$key] = $this->quoteIdentifierPrefixedKey($query->getTableAliasOrName(), $value);
            }
        }

        return $quoted;
    }

    protected function quoteIdentifiersFromSqlFunctionString(Query $query, string $string): string
    {
        $function = $this->extractSqlFunction($string);

        preg_match("#\((.+)\)#", $string, $values);

        if (!isset($values[1])) {
            throw new QueryException(sprintf(
                'Function %s requires a value to be passed.',
                $function
            ));
        }

        $values = explode(',', $values[1]);

        $quotedValues = StringBuilder::fromArray($this->quotePrefixedIdentifiersFromArray($query, $values), ',');

        return sprintf(
            '%s(%s)',
            $function,
            (string)$quotedValues
        );
    }

    protected function extractSqlFunction(string $string): ?string
    {
        foreach (['COUNT', 'SUM', 'AVG', 'MIN', 'MAX'] as $function) {
            if (stripos($string, $function) === 0) {
                return $function;
            }
        }

        return null;
    }
}
