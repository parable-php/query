<?php

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;

trait SupportsGroupByTrait
{
    protected function buildGroupBy(Query $query): string
    {
        if (count($query->getGroupBy()) === 0) {
            return '';
        }

        return sprintf(
            'GROUP BY %s',
            implode(', ', $this->quotePrefixedIdentifiersFromArray($query, $query->getGroupBy()))
        );
    }
}
