<?php

declare(strict_types=1);

namespace BjyProfiler;

class Module
{
    public function getConfig() : array
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
