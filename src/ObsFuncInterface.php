<?php

declare(strict_types=1);

namespace Rabbit\Observer;

interface ObsFuncInterface
{
    public function beforeHook(string $joinpoint, array $args, mixed $ret): void;
    public function afterHook(string $joinpoint, array $args, mixed $ret): void;
}
