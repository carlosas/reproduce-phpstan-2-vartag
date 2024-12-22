<?php

declare(strict_types=1);

namespace App;

class Example
{
    private string $name;

    private int $age;

    public function doSomething(): void
    {
        /** @var null|\DateTime $random */
        $random = rand(0, 1) === 1 ? new \DateTime() : null;
    }
}
