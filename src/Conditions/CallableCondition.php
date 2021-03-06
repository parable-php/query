<?php declare(strict_types=1);

namespace Parable\Query\Conditions;

class CallableCondition extends AbstractCondition
{
    /** @var callable */
    protected $callable;

    public function __construct(
        callable $callable,
        ?string $type = null,
        bool $valueIsKey = false
    ) {
        $this->callable = $callable;

        if ($type !== null) {
            $this->setType($type);
        }

        $this->setValueIsKey($valueIsKey);
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
