<?php

declare(strict_types=1);

namespace Zanzara\Middleware;

use Psr\Container\ContainerInterface;
use Zanzara\Support\CallableResolver;

/**
 * Middleware is a LIFO (Last In First Out) stack.
 */
abstract class MiddlewareCollector
{
    use CallableResolver;

    /**
     * Tip of the middleware stack.
     */
    protected MiddlewareNode $tip;

    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    /**
     * Last in, first out.
     *
     * @param MiddlewareInterface|callable $middleware
     * @return MiddlewareCollector
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function middleware($middleware): self
    {
        $next = $this->tip;
        $node = new MiddlewareNode($this->getCallable($middleware), $next);
        $this->tip = $node;
        return $this;
    }
}
