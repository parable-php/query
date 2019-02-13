<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;

trait SupportsWhereTrait
{
    protected function buildWhere(Query $query, $recursion = 0): string
    {
        if (empty($query->getWhereConditions())) {
            return '';
        }

        $parts = $this->buildConditions($query, $query->getWhereConditions(), $recursion);

        if ($recursion === 0) {
            array_unshift($parts, 'WHERE');
        }

        return implode(' ', $parts);
    }
}
