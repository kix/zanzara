<?php

declare(strict_types=1);

namespace Zanzara;

use function Opis\Closure\{serialize, unserialize};
use React\Promise\PromiseInterface;

class ConversationManager
{
    private const CONVERSATION = 'CONVERSATION';
    private const HANDLER_KEY = 'HANDLER';

    public function __construct(
        private readonly ZanzaraCache $cache,
        private readonly Config $config
    ) {}

    /**
     * Get key of the conversation by chatId
     */
    private static function resolveKey(?int $chatId = null, ?string $key = null): string
    {
        $res = self::CONVERSATION . '@' . $chatId;

        if ($key !== null) {
            $res .= "@$key";
        }

        return $res;
    }

    /**
     * @TODO What gets passed in $handler? `unserialize()` is involved, might be a security issue
     */
    public function setConversationHandler(int $chatId, $handler, bool $skipListeners, bool $skipMiddlewares): PromiseInterface
    {
        return $this->cache->set(self::resolveKey($chatId, self::HANDLER_KEY), [serialize($handler), $skipListeners, $skipMiddlewares], $this->config->getConversationTtl());
    }

    public function getConversationHandler(int $chatId): PromiseInterface
    {
        return $this->cache->get(self::resolveKey($chatId, self::HANDLER_KEY))
            ->then(static function ($conversation): ?array {
                if (!$conversation) {
                    return null;
                }

                $handler = $conversation[0];
                $handler = unserialize($handler);

                return [$handler, $conversation[1], $conversation[2]];
            });
    }

    /**
     * delete a cache item and return the promise
     */
    public function deleteConversationHandler(int $chatId): PromiseInterface
    {
        return $this->cache->delete(self::resolveKey($chatId, self::HANDLER_KEY));
    }
}
