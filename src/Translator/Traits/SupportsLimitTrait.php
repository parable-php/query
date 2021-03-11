<?php declare(strict_types=1);

namespace Parable\Query\Translator\Traits;

use Parable\Query\Query;
use Parable\Query\StringBuilder;

trait SupportsLimitTrait
{
    protected function buildLimit(Query $query): string
    {
        if ($query->getLimit() === null) {
            return '';
        }

        $queryParts = StringBuilder::fromArray([$query->getLimit(), $query->getOffset()], ',');

        return sprintf(
            'LIMIT %s',
            (string)$queryParts
        );
    }
}
