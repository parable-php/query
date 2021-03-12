<?php declare(strict_types=1);

namespace Parable\Query\Translators;

use Parable\Query\Query;

interface TranslatorInterface
{
    public function accepts(Query $query): bool;

    public function translate(Query $query): string;
}
