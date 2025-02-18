<?php

declare(strict_types=1);

namespace Zanzara\Test\JsonMapper\Issue;

use Zanzara\Test\JsonMapper\Issue\Element\Element;

class RootObject
{

    /**
     * @var Element[]
     */
    private $arrayOfElements;

    /**
     * @return Element[]
     */
    public function getArrayOfElements(): array
    {
        return $this->arrayOfElements;
    }

    /**
     * @param Element[] $arrayOfElements
     */
    public function setArrayOfElements(array $arrayOfElements): void
    {
        $this->arrayOfElements = $arrayOfElements;
    }

}
