<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;
use Parable\Query\StringBuilder;

trait SupportsGroupByTrait
{
    protected function buildGroupBy(Query $query): string
    {
        if (!$query->hasGroupBy()) {
            return '';
        }

        $groupParts = StringBuilder::fromArray($this->quotePrefixedIdentifiersFromArray($query, $query->getGroupBy()), ', ');

        return sprintf(
            'GROUP BY %s',
            $groupParts->toString()
        );
    }
}
