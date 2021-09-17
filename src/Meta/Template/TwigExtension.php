<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Meta\Template;

use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('static', [$this, 'staticCall']),
            new TwigFunction('addslashes', 'addslashes'),
        ];
    }

    /**
     * @param string $class
     * @param string $method
     * @param mixed  ...$arguments
     *
     * @return mixed
     */
    public function staticCall(string $class, string $method, ...$arguments)
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Cannot call static method $method on $class: class does not exist.");
        }

        if (!is_callable([$class, $method]) || !method_exists($class, $method)) {
            throw new RuntimeException("Cannot call static method $method on $class: method does not exist.");
        }

        return $class::$method(...$arguments);
    }
}
