<?php

declare(strict_types=1);

namespace Zanzara\Telegram;

use Psr\Container\ContainerInterface;
use React\Http\Browser;

/**
 *
 */
class Telegram
{
    use TelegramTrait;

    protected Browser $browser;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->browser = $container->get(Browser::class);
    }
}
