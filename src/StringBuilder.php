<?php declare(strict_types=1);

namespace Parable\Query;

class StringBuilder
{
    protected const DEFAULT_GLUE = ' ';

    /** @var string[] */
    protected array $parts = [];

    public function __construct(
        protected string $glue = self::DEFAULT_GLUE
    ) {}

    public function prepend(...$parts): void
    {
        array_unshift($this->parts, ...$parts);
    }

    public function add(...$parts): void
    {
        array_push($this->parts, ...$parts);
    }

    public function getGlue(): string
    {
        return $this->glue;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function isEmpty(): bool
    {
        return $this->parts === [];
    }

    public function merge(self $queryParts): self
    {
        if ($this->glue !== $queryParts->getGlue()) {
            throw new QueryException('Cannot merge StringBuilder with different glues.');
        }

        if ($queryParts->isEmpty()) {
            return $this;
        }

        $this->add(...$queryParts->getParts());

        return $this;
    }

    public function __toString(): string
    {
        return trim(implode($this->glue, array_filter($this->parts)));
    }

    public static function fromArray(array $parts, string $glue = self::DEFAULT_GLUE): self
    {
        $queryParts = new self($glue);

        $queryParts->add(...array_values($parts));

        return $queryParts;
    }
}
