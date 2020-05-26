<?php declare(strict_types=1);

namespace Parable\Query;

class ValueSet
{
    /** @var array */
    protected $values = [];

    public function __construct(array $values = [])
    {
        foreach ($values as $key => $value) {
            $this->addValue($key, $value);
        }
    }

    public function addValue(string $key, $value): self
    {
        if (!is_scalar($value) && !is_null($value)) {
            throw new Exception(sprintf(
                'Value is of invalid type: %s',
                gettype($value)
            ));
        }

        $this->values[$key] = $value;

        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getKeys(): array
    {
        return array_keys($this->values);
    }

    public function hasValues(): bool
    {
        return count($this->values) > 0;
    }
}
