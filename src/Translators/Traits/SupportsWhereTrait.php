<?php declare(strict_types=1);

namespace Parable\Query\Translators\Traits;

use Parable\Query\Query;

trait SupportsWhereTrait
{
    protected function buildWhere(Query $query, $recursion = 0): string
    {
        if (!$query->hasWhereConditions()) {
            return '';
        }

        $conditionParts = $this->buildConditions($query, $query->getWhereConditions(), $recursion);

        if ($recursion === 0) {
            $conditionParts->prepend('WHERE');
        }

        return (string)$conditionParts;
    }
}
