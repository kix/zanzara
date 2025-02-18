<?php

declare(strict_types=1);

namespace Zanzara\UpdateMode;

abstract class BaseWebhook extends UpdateMode
{
    private function isSafeMode(): bool
    {
        return $this->config->getSafeMode();
    }

    protected function isWebhookAuthorized(string $path): bool
    {
        if (!$this->config->isWebhookTokenCheckEnabled()) {
            return true;
        }

        return $this->resolveTokenFromPath($path) === $this->config->getBotToken();
    }

    protected function resolveTokenFromPath(string $path): ?string
    {
        $pathParams = explode('/', $path);
        return end($pathParams) ?? null;
    }

    protected function verifyTelegramIpSrc(string $ipAsString): bool
    {
        if ($this->isSafeMode()) {
            return true;
        }

        $ipAsLong = ip2long($ipAsString);

        if (!$ipAsLong) {
            return false;
        }

        foreach ($this->config->getTelegramIpv4Ranges() as $lower => $upper) {
            // Make sure the IPv4 is valid telegram ip.
            if ($ipAsLong >= ip2long($lower) && $ipAsLong <= ip2long($upper)) {
                return true;
            }
        }

        $this->logger->errorNotAuthorizedIp(long2ip($ipAsLong));
        return false;
    }

    protected function verifyRequestMethod(string $method): bool
    {
        if ($this->isSafeMode()) {
            return true;
        }

        if ($method !== 'POST') {
            $this->logger->errorNotAuthorizedRequestMethod();
            return false;
        }
        return true;
    }

    protected function verifyAuthorizedWebHook(string $path): bool
    {
        if (!$this->isWebhookAuthorized($path)) {
            $this->logger->errorNotAuthorized();
            return false;
        }
        return true;
    }
}
