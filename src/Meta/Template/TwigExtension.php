<?php declare(strict_types=1);

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

    public function staticCall($class, $method, ...$args)
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Cannot call static method $method on $class: class does not exist.");
        }

        if (!method_exists($class, $method)) {
            throw new RuntimeException("Cannot call static method $method on $class: method does not exist.");
        }

        return forward_static_call_array([$class, $method], $args);
    }
}