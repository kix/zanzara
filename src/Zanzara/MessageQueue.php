<?php

declare(strict_types=1);

namespace Zanzara;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Zanzara\Telegram\Telegram;
use Zanzara\Telegram\Type\Response\TelegramException;

class MessageQueue
{
    /**
     * @TODO Remove logger; it's not used
     */
    public function __construct(
        private readonly Telegram $telegram,
        private ZanzaraLogger $logger,
        private readonly LoopInterface $loop,
        private readonly Config $config
    ) {}

    public function push(array $chatIds, string $text, array $opt = []): void
    {
        $payload = []; // indexed array of Telegram params
        $opt['text'] = $text;
        // prepare the params array for each chatId
        foreach ($chatIds as $chatId) {
            $clone = $opt;
            $clone['chat_id'] = $chatId;
            $payload[] = $clone;
        }
        $dequeue = function (TimerInterface $timer) use (&$payload) {
            // if there's no more message to dequeue cancel the timer
            if (!$payload) {
                $this->loop->cancelTimer($timer);
                return;
            }

            // pop and process
            $params = array_pop($payload);
            $this->telegram->doSendMessage($params)->/** @scrutinizer ignore-call */ otherwise(function (TelegramException $error) {
                $this->logger->error("Failed to send message in bulk mode, reason: $error");
            });
        };
        $this->loop->addPeriodicTimer($this->config->getBulkMessageInterval(), $dequeue);
    }
}
