<?php

namespace Framework\Router;

interface GroupInterface
{
    /**
     * @return GroupInterface
     */
    public function getRoutes(): GroupInterface;
}