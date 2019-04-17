<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Exception;
use Parable\Query\Query;
use PDO;

abstract class AbstractTranslator
{
    /**
     * @var PDO
     */
    protected $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    abstract public function accepts(Query $query): bool;

    abstract public function translate(Query $query): string;

    public function quote($value): string
    {
        return $this->connection->quote((string)$value);
    }

    public function quoteIdentifier(string $string): string
    {
        return '`' . trim($string, '`') . '`';
    }

    public function quoteIdentifierPrefixedKey(string $tableName, string $key): string
    {
        return sprintf(
            '%s.%s',
            $this->quoteIdentifier($tableName),
            $this->quoteIdentifier($key)
        );
    }

    public function quoteValuesFromArray(array $array): array
    {
        $quoted = [];

        foreach ($array as $key => $value) {
            $quoted[$key] = $this->quote($value);
        }

        return $quoted;
    }

    public function quoteIdentifiersFromArray(array $array): array
    {
        $quoted = [];

        foreach ($array as $key) {
            $quoted[] = $this->quoteIdentifier($key);
        }

        return $quoted;
    }

    public function quotePrefixedIdentifiersFromArray(Query $query, array $array): array
    {
        $quoted = [];

        foreach ($array as $key => $value) {
            if ($value === '*' || is_numeric($value)) {
                $quoted[$key] = $value;
            } elseif ($this->extractSqlFunction($value) !== null) {
                $quoted[$key] = $this->quoteIdentifiersFromSqlFunctionString($query, $value);
            } elseif (strpos($value, '.') !== false) {
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
            throw new Exception(sprintf(
                'Function %s requires a value to be passed.',
                $function
            ));
        }

        $values = explode(',', $values[1]);

        $quotedValues = $this->quotePrefixedIdentifiersFromArray($query, $values);

        return sprintf(
            '%s(%s)',
            $function,
            implode(',', $quotedValues)
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
