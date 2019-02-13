<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Exception;
use Parable\Query\Query;

trait SupportsOrderByTrait
{
    protected function buildOrderBy(Query $query): string
    {
        if (count($query->getOrderBy()) === 0) {
            return '';
        }

        $parts = [];

        foreach ($query->getOrderBy() as $key => $direction) {
            if (!in_array($direction, [Query::ORDER_ASC, Query::ORDER_DESC])) {
                throw new Exception('Order direction ' . $direction . ' is invalid.');
            }

            $quoted = $this->quotePrefixedIdentifiersFromArray($query, [$key]);

            $parts[] = sprintf(
                '%s %s',
                reset($quoted),
                $direction
            );
        }

        return sprintf(
            'ORDER BY %s',
            implode(', ', $parts)
        );
    }
}
