<?php

declare(strict_types=1);

namespace Zanzara\UpdateMode;

interface UpdateModeInterface
{
    public function run(): void;
}
