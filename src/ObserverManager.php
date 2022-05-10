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
        ], [$func, 'beforeHook']);

        EasyAop::add_advice([
            "ahook@{$name}",
        ], [$func, 'afterHook']);
    }

    public function delObserver(string $name): void
    {
        EasyAop::del_hook([
            "bhook@{$name}",
            "ahook@{$name}",
        ]);
    }

    public function updateObserver(string $name, ObsFuncInterface $func): void
    {
        $this->delObserver($name);
        $this->addObserver($name, $func);
    }

    public function getJoinPoint(): array
    {
        $arr = [];
        foreach (get_declared_classes() as $class) {
            $arr['object'][$class] = get_class_methods($class);
        }

        $arr['func'] = get_defined_functions()['user'];
        return $arr;
    }
}
