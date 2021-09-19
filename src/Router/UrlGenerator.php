<?php

namespace Framework\Router;

use FastRoute\RouteParser\Std as RouteParser;
use RuntimeException;

class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var RouteCollectorInterface
     */
    protected RouteCollectorInterface $routeCollector;

    /**
     * @var RouteParser
     */
    protected RouteParser $routeParser;

    /**
     * @var string
     */
    protected string $basePath;

    /**
     * @param RouteCollectorInterface $routeCollector
     * @param string $basePath
     */
    public function __construct(RouteCollectorInterface $routeCollector, string $basePath = '')
    {
        $this->routeCollector = $routeCollector;
        $this->routeParser = new RouteParser();
        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl(string $name, array $attributes = [], array $queryParams = []): string
    {
        return $this->generatePath($name, $attributes, $queryParams);
    }

    /**
     * {@inheritDoc}
     */
    public function generatePath(string $name, array $attributes = [], array $queryParams = []): string
    {
        $route = $this->getRoute($name);

        $routePartSets = array_reverse($this->routeParser->parse($route->getPath()));

        $routeIndex = $this->getRouteIndex($routePartSets, $attributes);

        $path = $this->generatePathFromAttributes($name, $routePartSets, $attributes, $routeIndex);

        if ([] === $queryParams) {
            return $this->basePath . $path;
        }

        return $this->basePath . $path . '?' . http_build_query($queryParams);
    }

    /**
     * @param string $name
     * @return RouteInterface
     * @throws RuntimeException
     */
    protected function getRoute(string $name): RouteInterface
    {
        try {
            $this->routeCollector->getNamedRoute($name);
        } catch (RuntimeException $exception) {
            throw $exception;
        }

        return $this->routeCollector->getNamedRoute($name);
    }

    /**
     * @param array<int, array<int, array|string>> $routePartSets
     * @param array<string> $attributes
     * @return ?int
     */
    protected function getRouteIndex(array $routePartSets, array $attributes)
    {
        foreach ($routePartSets as $routeIndex => $routeParts) {
            foreach ($routeParts as $routePart) {
                if (is_array($routePart)) {
                    $parameter = $routePart[0];

                    if (!isset($attributes[$parameter])) {
                        continue 2;
                    }
                }
            }

            return $routeIndex;
        }

        return array_key_last($routePartSets);
    }

    /**
     * @param string $name
     * @param array<int, array<int, array|string>> $routePartSets
     * @param array<string> $attributes
     * @param ?int $routeIndex
     * @return string
     */
    protected function generatePathFromAttributes(
        string $name,
        array $routePartSets,
        array $attributes,
        ?int $routeIndex
    ) {
        $pathParts = [];

        foreach ($routePartSets[$routeIndex] as $routePart) {
            if (is_array($routePart)) {
                $pathParts[] = $this->getAttributeValue($name, $routePart, $attributes);
            } else {
                $pathParts[] = $routePart;
            }
        }

        return implode('', $pathParts);
    }

    /**
     * @param string $name
     * @param array<int, string> $routePart
     * @param array<string> $attributes
     * @return string
     */
    protected function getAttributeValue(string $name, array $routePart, array $attributes): string
    {
        $attribute = $routePart[0];

        if (!isset($attributes[$attribute])) {
            throw new RuntimeException(
                sprintf(
                    'Missing attribute "%s" while path generation for route: "%s"',
                    $attribute,
                    $name
                )
            );
        }

        $value = (string) $attributes[$attribute];
        $pattern = '!^' . $routePart[1] . '$!';

        if (1 !== preg_match($pattern, $value)) {
            throw new RuntimeException(
                sprintf(
                    'Not matching value "%s" with pattern "%s" on attr "%s" while path generation for route: "%s"',
                    $name,
                    $attribute,
                    $value,
                    $routePart[1]
                )
            );
        }

        return $value;
    }
}
