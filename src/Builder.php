<?php

namespace Parable\Query;

use Parable\Query\Translator\AbstractTranslator;
use Parable\Query\Translator\DeleteTranslator;
use Parable\Query\Translator\InsertTranslator;
use Parable\Query\Translator\SelectTranslator;
use Parable\Query\Translator\UpdateTranslator;
use PDO;

class Builder
{
    /**
     * @var PDO
     */
    protected $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function build(Query $query): string
    {
        foreach ($this->getTranslators() as $translatorClass) {
            /** @var AbstractTranslator $translator */
            $translator = new $translatorClass($this->connection);

            if (!$translator->accepts($query)) {
                continue;
            }

            return $translator->translate($query);
        }

        throw new Exception('Could not find suitable translater for query with type: ' . $query->getType());
    }

    /**
     * @return string[]
     */
    protected function getTranslators(): array
    {
        return [
            DeleteTranslator::class,
            InsertTranslator::class,
            UpdateTranslator::class,
            SelectTranslator::class,
        ];
    }
}
