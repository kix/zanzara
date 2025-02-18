<?php

declare(strict_types=1);

namespace Zanzara\Telegram;

use Psr\Container\ContainerInterface;
use React\Http\Browser;

class Telegram
{
    use TelegramTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->browser = $container->get(Browser::class);
    }
}
