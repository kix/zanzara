<?php

declare(strict_types=1);

namespace Zanzara\Middleware;

use Zanzara\Context;

/**
 * A node of the middleware stack.
 * The last node is the callback to be executed and does not have a next node.
 *
 */
class MiddlewareNode
{
    /**
     * @var MiddlewareInterface|callable
     */
    private $current;

    private ?MiddlewareNode $next = null;

    /**
     * @param MiddlewareInterface|callable $current
     * @param MiddlewareNode|null $next
     */
    public function __construct($current, ?MiddlewareNode $next = null)
    {
        $this->current = $current;
        $this->next = $next;
    }

    public function __invoke(Context $ctx): void
    {
        if ($this->current instanceof MiddlewareInterface) {
            $this->current->handle($ctx, $this->next);
        } else {
            call_user_func($this->current, $ctx, $this->next);
        }
    }
}
