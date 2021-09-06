<?php

namespace Framework\Factory;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;

class EmitterFactory
{
    /**
     * @return EmitterInterface
     */
    public function __invoke()
    {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());
        $stack->push(new SapiStreamEmitter());

        return $stack;
    }
}