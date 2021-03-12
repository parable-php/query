<?php declare(strict_types=1);

namespace Parable\Query\Translators\Traits;

use Parable\Query\QueryException;
use Parable\Query\Query;
use Parable\Query\StringBuilder;

trait SupportsOrderByTrait
{
    protected function buildOrderBy(Query $query): string
    {
        if (!$query->hasOrderBy()) {
            return '';
        }

        $parts = [];

        foreach ($query->getOrderBy() as $orderBy) {
            $quotedKeys = $this->quotePrefixedIdentifiersFromArray($query, $orderBy->getKeys());

            foreach ($quotedKeys as $key) {
                if (isset($parts[$key]) && strpos($parts[$key], $orderBy->getDirection()) === false) {
                    throw new QueryException('Cannot define order by key twice with different directions.');
                }

                $parts[$key] = sprintf(
                    '%s %s',
                    $key,
                    $orderBy->getDirection()
                );
            }
        }

        return sprintf(
            'ORDER BY %s',
            (string)StringBuilder::fromArray($parts, ', ')
        );
    }
}
