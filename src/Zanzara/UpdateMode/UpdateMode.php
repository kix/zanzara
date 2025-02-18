<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */
/** @noinspection PhpVoidFunctionResultUsedInspection */

declare(strict_types=1);

namespace Zanzara\UpdateMode;

use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use Zanzara\Config;
use Zanzara\Listener\Listener;
use Zanzara\Telegram\Telegram;
use Zanzara\Telegram\Type\Update;
use Zanzara\Zanzara;
use Zanzara\ZanzaraLogger;
use Zanzara\ZanzaraMapper;

abstract class UpdateMode implements UpdateModeInterface
{
    protected ContainerInterface $container;

    protected Zanzara $zanzara;

    protected Telegram $telegram;

    protected Config $config;

    protected ZanzaraLogger $logger;

    protected LoopInterface $loop;

    protected ZanzaraMapper $zanzaraMapper;

    public function __construct(ContainerInterface $container, Zanzara $zanzara, Telegram $telegram, Config $config, ZanzaraLogger $logger, LoopInterface $loop, ZanzaraMapper $zanzaraMapper)
    {
        $this->container = $container;
        $this->zanzara = $zanzara;
        $this->telegram = $telegram;
        $this->config = $config;
        $this->logger = $logger;
        $this->loop = $loop;
        $this->zanzaraMapper = $zanzaraMapper;
    }

    protected function processUpdate(Update $update): void
    {
        $update->detectUpdateType();
        $contextClass = $this->config->getContextClass();
        $context = new $contextClass($update, $this->container);
        $this->zanzara->resolveListeners($update)
            ->then(function ($listeners) use ($context) {
                /** @var Listener[] $listeners */
                foreach ($listeners as $listener) {
                    if ($listener instanceof Listener) {
                        $middlewareTip = $listener->getTip();
                        $middlewareTip($context);
                    }
                }
            })->otherwise(function ($e) use ($context, $update) {
                if (!$this->zanzara->callOnException($context, $e)) {
                    $this->logger->error("Unable to resolve listeners for update $update, reason: $e");
                }
            });
    }
}
