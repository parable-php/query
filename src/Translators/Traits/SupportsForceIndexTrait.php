<?php declare(strict_types=1);

namespace Parable\Query\Translators\Traits;

use Parable\Query\Query;

trait SupportsForceIndexTrait
{
    protected function buildForceIndex(Query $query): string
    {
        $forceIndex = $query->getForceIndex();

        if ($forceIndex === null) {
            return '';
        }

        if ($forceIndex === Query::PRIMARY_KEY_INDEX) {
            return 'FORCE INDEX (PRIMARY)';
        }

        return sprintf(
            "FORCE INDEX (%s)",
            $this->quote($forceIndex)
        );
    }
}
