<?php

declare(strict_types=1);

namespace Zanzara;

use DI\Container;
use Psr\Log\LoggerInterface;
use React\Cache\CacheInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Socket\Connector;
use Zanzara\UpdateMode\Polling;
use Zanzara\UpdateMode\ReactPHPWebhook;
use Zanzara\UpdateMode\UpdateModeInterface;
use Zanzara\UpdateMode\Webhook;

/**
 * TODO: This is a god class. Consider treating configs differently (e.g. read-only props in constructor
 * or some smart array handling)
 */
class Config
{
    public const WEBHOOK_MODE = Webhook::class;
    public const POLLING_MODE = Polling::class;
    public const REACTPHP_WEBHOOK_MODE = ReactPHPWebhook::class;

    public const PARSE_MODE_HTML = "HTML";
    public const PARSE_MODE_MARKDOWN = "MarkdownV2";
    public const PARSE_MODE_MARKDOWN_LEGACY = "Markdown";

    private string $botToken;

    private ?LoopInterface $loop;

    private ?CacheInterface $cache;

    private bool $useReactFileSystem = false;

    private ?Container $container;

    /**
     * @var string|UpdateModeInterface
     */
    private $updateMode = self::POLLING_MODE;

    private ?string $parseMode;

    private string $updateStream = 'php://input';

    private string $apiTelegramUrl = 'https://api.telegram.org';

    private string $serverUri = "0.0.0.0:8080";

    private array $serverContext = [];

    private float $bulkMessageInterval = 2.0;

    private bool $webhookTokenCheck = false;

    /**
     * Timeout in seconds for long polling. Defaults to 0, i.e. usual short polling. Should be positive, short polling
     * should be used for testing purposes only.
     */
    private int $pollingTimeout = 50;

    /**
     * Limits the number of updates to be retrieved. Values between 1-100 are accepted. Defaults to 100.
     */
    private int $pollingLimit = 100;

    /**
     * Defines when we have to retry after the processing of an update has given error.
     */
    private float $pollingRetry = 2.0;

    /**
     * A JSON-serialized list of the update types you want your bot to receive. For example, specify
     * [“message”, “edited_channel_post”, “callback_query”] to only receive updates of these types. See Update for a
     * complete list of available update types. Specify an empty list to receive all updates regardless of type
     * (default). If not specified, the previous setting will be used. Please note that this parameter doesn't affect
     * updates created before the call to the getUpdates, so unwanted updates may be received for a short period of time.
     */
    private array $pollingAllowedUpdates = [];

    private ?LoggerInterface $logger;

    private bool $disableZanzaraLogger = false;

    /**
     * @var callable|null
     */
    private $errorHandler;

    /**
     * Default ttl in seconds. Null means that item will stay in the cache
     * for as long as the underlying implementation supports.
     * Check reactphp cache implementation for more information
     */
    private ?float $cacheTtl = 180;

    private ?float $conversationTtl = 60 * 60 * 24;

    private ?Connector $connector;

    /**
     * @since 0.5.1
     */
    private array $connectorOptions = [];

    /**
     *
     * @since 0.5.1
     */
    private ?string $proxyUrl;

    /**
     * @since 0.5.1
     */
    private array $proxyHttpHeaders = [];

    private ?Browser $browser;

    private string $contextClass = Context::class;

    private bool $safeMode = false;

    private array $telegramIpv4Ranges = [
        '149.154.160.0' => '149.154.175.255', // literally 149.154.160.0/20
        '91.108.4.0' => '91.108.7.255',    // literally 91.108.4.0/22
    ];

    public function getLoop(): ?LoopInterface
    {
        return $this->loop;
    }

    public function setLoop(?LoopInterface $loop): void
    {
        $this->loop = $loop;
    }

    /**
     * @return string|UpdateModeInterface
     */
    public function getUpdateMode()
    {
        return $this->updateMode;
    }

    public function setUpdateMode(string $updateMode): void
    {
        $this->updateMode = $updateMode;
    }

    public function getParseMode(): ?string
    {
        return $this->parseMode;
    }

    public function setParseMode(?string $parseMode): void
    {
        $this->parseMode = $parseMode;
    }

    public function getUpdateStream(): string
    {
        return $this->updateStream;
    }

    public function setUpdateStream(string $updateStream): void
    {
        $this->updateStream = $updateStream;
    }

    public function getApiTelegramUrl(): string
    {
        return $this->apiTelegramUrl;
    }

    public function setApiTelegramUrl(string $apiTelegramUrl): void
    {
        $this->apiTelegramUrl = $apiTelegramUrl;
    }

    public function getServerUri(): string
    {
        return $this->serverUri;
    }

    public function setServerUri(string $serverUri): void
    {
        $this->serverUri = $serverUri;
    }

    public function getServerContext(): array
    {
        return $this->serverContext;
    }

    public function setServerContext(array $serverContext): void
    {
        $this->serverContext = $serverContext;
    }

    public function getBulkMessageInterval(): float
    {
        return $this->bulkMessageInterval;
    }

    public function setBulkMessageInterval(float $bulkMessageInterval): void
    {
        $this->bulkMessageInterval = $bulkMessageInterval;
    }

    public function isWebhookTokenCheckEnabled(): bool
    {
        return $this->webhookTokenCheck;
    }

    public function enableWebhookTokenCheck(bool $webhookTokenCheck): void
    {
        $this->webhookTokenCheck = $webhookTokenCheck;
    }

    public function getPollingTimeout(): int
    {
        return $this->pollingTimeout;
    }

    public function setPollingTimeout(int $pollingTimeout): void
    {
        $this->pollingTimeout = $pollingTimeout;
    }

    public function getPollingLimit(): int
    {
        return $this->pollingLimit;
    }

    public function setPollingLimit(int $pollingLimit): void
    {
        $this->pollingLimit = $pollingLimit;
    }

    public function getPollingAllowedUpdates(): array
    {
        return $this->pollingAllowedUpdates;
    }

    public function setPollingAllowedUpdates(array $pollingAllowedUpdates): void
    {
        $this->pollingAllowedUpdates = $pollingAllowedUpdates;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger, bool $disableZanzaraLogger = false): void
    {
        $this->logger = $logger;
        $this->disableZanzaraLogger = $disableZanzaraLogger;
    }

    public function getDisableZanzaraLogger(): bool
    {
        return $this->disableZanzaraLogger;
    }

    public function setDisableZanzaraLogger(bool $bool): void
    {
        $this->disableZanzaraLogger = $bool;
    }

    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function setContainer(?Container $container): void
    {
        $this->container = $container;
    }

    public function getBotToken(): string
    {
        return $this->botToken;
    }

    public function setBotToken(string $botToken): void
    {
        $this->botToken = $botToken;
    }

    public function useReactFileSystem(bool $bool)
    {
        $this->useReactFileSystem = $bool;
    }

    public function isReactFileSystem(): bool
    {
        return $this->useReactFileSystem;
    }

    /**
     * @deprecated
     * @see Zanzara::callOnException()
     */
    public function getErrorHandler(): ?callable
    {
        return $this->errorHandler;
    }

    /**
     * @deprecated use Zanzara::onException() instead.
     * @see Zanzara::onException()
     */
    public function setErrorHandler(?callable $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    public function getCacheTtl(): ?float
    {
        return $this->cacheTtl;
    }

    public function setCacheTtl(?float $cacheTtl): void
    {
        $this->cacheTtl = $cacheTtl;
    }

    public function getConnector(): ?Connector
    {
        return $this->connector;
    }

    public function setConnector(?Connector $connector): void
    {
        $this->connector = $connector;
    }

    public function getConnectorOptions(): array
    {
        return $this->connectorOptions;
    }

    public function setConnectorOptions(array $connectorOptions): void
    {
        $this->connectorOptions = $connectorOptions;
    }

    public function getProxyUrl(): ?string
    {
        return $this->proxyUrl;
    }

    public function setProxyUrl(?string $proxyUrl): void
    {
        $this->proxyUrl = $proxyUrl;
    }

    public function getProxyHttpHeaders(): array
    {
        return $this->proxyHttpHeaders;
    }

    public function setProxyHttpHeaders(array $proxyHttpHeaders): void
    {
        $this->proxyHttpHeaders = $proxyHttpHeaders;
    }

    public function getBrowser(): ?Browser
    {
        return $this->browser;
    }

    public function setBrowser(?Browser $browser): void
    {
        $this->browser = $browser;
    }

    public function getConversationTtl(): ?float
    {
        return $this->conversationTtl;
    }

    public function setConversationTtl(?float $conversationTtl): void
    {
        $this->conversationTtl = $conversationTtl;
    }

    public function getPollingRetry(): float
    {
        return $this->pollingRetry;
    }

    public function setPollingRetry(float $pollingRetry): void
    {
        $this->pollingRetry = $pollingRetry;
    }

    public function getContextClass(): string
    {
        return $this->contextClass;
    }

    public function setContextClass(string $contextClass): void
    {
        $this->contextClass = $contextClass;
    }

    public function setSafeMode(bool $mode): void
    {
        $this->safeMode = $mode;
    }

    public function getSafeMode(): bool
    {
        return $this->safeMode;
    }

    public function getTelegramIpv4Ranges(): array
    {
        return $this->telegramIpv4Ranges;
    }

    public function setTelegramIpv4Ranges(array $telegramIpv4Ranges): void
    {
        $this->telegramIpv4Ranges = $telegramIpv4Ranges;
    }
}
