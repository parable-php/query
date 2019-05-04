<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;

trait SupportsGroupByTrait
{
    protected function buildGroupBy(Query $query): string
    {
        if (!$query->hasGroupBy()) {
            return '';
        }

        return sprintf(
            'GROUP BY %s',
            implode(', ', $this->quotePrefixedIdentifiersFromArray($query, $query->getGroupBy()))
        );
    }
}
