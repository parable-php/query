<?php declare(strict_types=1);

namespace Parable\Query;

interface TranslatorInterface
{
    public function accepts(Query $query): bool;

    public function translate(Query $query): string;
}
