<?php

namespace Parable\Query\Condition;

class CallableCondition extends AbstractCondition
{
    /**
     * @var callable
     */
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
