<?php

namespace Parable\Query\Tests\Classes;

use Parable\Query\Query;
use Parable\Query\Translators\TranslatorInterface;

class NonsenseTranslator implements TranslatorInterface
{
    public function accepts(Query $query): bool
    {
        return $query->getType() === 'NONSENSE';
    }

    public function translate(Query $query): string
    {
        return 'Nonsense query!';
    }
}
