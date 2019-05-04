<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Exception;
use Parable\Query\Query;

trait SupportsOrderByTrait
{
    protected function buildOrderBy(Query $query): string
    {
        if (!$query->hasOrderBy()) {
            return '';
        }

        $parts = [];

        foreach ($query->getOrderBy() as $order) {
            $quotedKeys = $this->quotePrefixedIdentifiersFromArray($query, $order->getKeys());

            foreach ($quotedKeys as $key) {
                if (isset($parts[$key]) && strpos($parts[$key], $order->getDirectionAsString()) === false) {
                    throw new Exception('Cannot define order by key twice with different directions.');
                }

                $parts[$key] = sprintf(
                    '%s %s',
                    $key,
                    $order->getDirectionAsString()
                );
            }
        }

        return sprintf(
            'ORDER BY %s',
            implode(', ', $parts)
        );
    }
}
