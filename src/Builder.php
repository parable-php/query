<?php declare(strict_types=1);

namespace Parable\Query;

use Parable\Query\Translators\DeleteTranslator;
use Parable\Query\Translators\InsertTranslator;
use Parable\Query\Translators\SelectTranslator;
use Parable\Query\Translators\TranslatorInterface;
use Parable\Query\Translators\UpdateTranslator;
use PDO;

class Builder
{
    public function __construct(
        protected PDO $connection
    ) {}

    public function build(Query $query): string
    {
        foreach ($this->getTranslators() as $translatorClass) {
            /** @var TranslatorInterface $translator */
            $translator = new $translatorClass($this->connection);

            if (!$translator->accepts($query)) {
                continue;
            }

            return $translator->translate($query);
        }

        throw new QueryException(
            'Could not find suitable translator for query with type: ' . $query->getType()
        );
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
