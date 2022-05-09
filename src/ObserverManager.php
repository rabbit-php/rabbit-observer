<?php

declare(strict_types=1);

namespace Rabbit\Observer;

use EasyAop;

class ObserverManager
{
    public function addObserver(string $name, ObsFuncInterface $func): void
    {
        EasyAop::add_advice([
            "bhook@{$name}",
        ], function (string $joinpoint, array $args, mixed $ret) use ($func): void {
            $func->beforeHook($joinpoint, $args, $ret);
        });

        EasyAop::add_advice([
            "ahook@{$name}",
        ], function (string $joinpoint, array $args, mixed $ret) use ($func): void {
            $func->afterHook($joinpoint, $args, $ret);
        });
    }

    public function delObserver(string $name): void
    {
        EasyAop::add_advice([
            "bhook@{$name}",
            "ahook@{$name}",
        ]);
    }

    public function updateObserver(string $name, ObsFuncInterface $func): void
    {
        $this->delObserver($name);
        $this->addObserver($name, $func);
    }
}
