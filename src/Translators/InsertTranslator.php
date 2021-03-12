<?php declare(strict_types=1);

namespace Parable\Query\Translators;

use Parable\Query\Query;
use Parable\Query\StringBuilder;
use Parable\Query\Translators\Traits\SupportsValuesTrait;
use Parable\Query\Translators\TranslatorInterface;

class InsertTranslator extends AbstractTranslator implements TranslatorInterface
{
    use SupportsValuesTrait;

    public function accepts(Query $query): bool
    {
        return $query->getType() === Query::TYPE_INSERT;
    }

    public function translate(Query $query): string
    {
        $queryParts = new StringBuilder();

        $queryParts->add(
            'INSERT INTO',
            $this->quoteIdentifier($query->getTableName()),
            $this->buildValues($query)
        );

        return (string)$queryParts;
    }
}
