<?php declare(strict_types=1);

namespace Parable\Query\Translator;

use Parable\Query\Query;
use Parable\Query\Translator\Traits\SupportsValuesTrait;
use Parable\Query\TranslatorInterface;

class InsertTranslator extends AbstractTranslator implements TranslatorInterface
{
    use SupportsValuesTrait;

    public function accepts(Query $query): bool
    {
        return $query->getType() === Query::TYPE_INSERT;
    }

    public function translate(Query $query): string
    {
        $parts = [
            'INSERT INTO',
            $this->quoteIdentifier($query->getTableName()),
            $this->buildValues($query),
        ];

        return trim(implode(' ', array_filter($parts)));
    }
}
