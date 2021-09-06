<?php

namespace Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouteMatcherInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return RouterResults
     */
    public function match(ServerRequestInterface $request): RouterResults;
}