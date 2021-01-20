<?php declare(strict_types=1);

namespace Parable\Query;

class OrderBy
{
    protected const ORDER_ASC = 'ASC';
    protected const ORDER_DESC = 'DESC';

    protected string $direction;

    /** @var string[] */
    protected array $keys = [];

    protected function __construct(string $direction, array $keys)
    {
        $this->direction = $direction;

        if (count($keys) === 0) {
            throw new Exception('Cannot create order without keys.');
        }

        $this->keys = $keys;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function isAscending(): bool
    {
        return $this->direction === self::ORDER_ASC;
    }

    public function isDescending(): bool
    {
        return $this->direction === self::ORDER_DESC;
    }

    public static function asc(string ...$keys): self
    {
        return new self(self::ORDER_ASC, $keys);
    }

    public static function desc(string ...$keys): self
    {
        return new self(self::ORDER_DESC, $keys);
    }
}
