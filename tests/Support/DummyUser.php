<?php

declare(strict_types=1);

namespace TimDev\Test\Log\Support;

class DummyUser
{
    public function getName(): string
    {
        return 'Some Dummy';
    }

    public function getId(): int
    {
        return 8675309;
    }
}
