<?php declare(strict_types=1);

namespace Parable\Query;

class OrderBy
{
    protected const ORDER_ASC = 1;
    protected const ORDER_DESC = 2;

    /**
     * @var int
     */
    protected $direction;

    /**
     * @var string[]
     */
    protected $keys = [];

    protected function __construct(int $direction, array $keys)
    {
        $this->direction = $direction;

        if (count($keys) === 0) {
            throw new Exception('Cannot create order without keys.');
        }

        $this->keys = $keys;
    }

    public function getDirectionAsString(): string
    {
        return $this->isAscending() ? 'ASC' : 'DESC';
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
