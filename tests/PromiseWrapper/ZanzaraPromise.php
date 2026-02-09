<?php

namespace Zanzara\Test\PromiseWrapper;

use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;
use Zanzara\ZanzaraMapper;

class ZanzaraPromise implements PromiseInterface
{
    private PromiseInterface $promise;

    private string $class;

    private ZanzaraMapper $zanzaraMapper;

    /**
     * PromiseWrapper constructor.
     * @param PromiseInterface $promise
     * @param string $class
     */
    public function __construct(PromiseInterface $promise, string $class)
    {
        $this->promise = $promise;
        $this->class = $class;
        $this->zanzaraMapper = new ZanzaraMapper(new \JsonMapper());
    }

    /**
     * @inheritDoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null): PromiseInterface
    {
        return $this->promise->then(
            function (ResponseInterface $response) use ($onFulfilled, $onRejected) {
                $json = (string) $response->getBody();
                $onFulfilled($this->zanzaraMapper->mapJson($json, $this->class));
            },
            $onRejected,
            $onProgress
        );
    }

    public function catch(callable $onRejected): PromiseInterface
    {
        return $this->promise->catch($onRejected);
    }

    public function finally(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->promise->finally($onFulfilledOrRejected);
    }

    public function cancel(): void
    {
        $this->promise->cancel();
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->promise->otherwise($onRejected);
    }

    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->promise->always($onFulfilledOrRejected);
    }
}
