<?php

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;

trait SupportsLimitTrait
{
    protected function buildLimit(Query $query): string
    {
        $string = '';

        if ($query->getLimit() !== null) {
            $string .= 'LIMIT ' . $query->getLimit();
        }

        if ($query->getLimit() !== null && $query->getOffset() !== null) {
            $string .= ',' . $query->getOffset();
        }

        return $string;
    }
}
