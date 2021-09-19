<?php

namespace Framework\Router;

use RuntimeException;

interface UrlGeneratorInterface
{
    /**
     * @param string $name
     * @param array<string, string> $attributes
     * @param array<string, mixed> $queryParams
     * @return string
     */
    public function generateUrl(
        string $name,
        array $attributes = [],
        array $queryParams = []
    ): string;

    /**
     * @param string $name
     * @param array<string, string> $attributes
     * @param array<string, mixed> $queryParams
     * @return string
     * @throws RuntimeException
     */
    public function generatePath(string $name, array $attributes = [], array $queryParams = []): string;
}
