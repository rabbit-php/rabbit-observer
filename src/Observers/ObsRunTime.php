<?php

declare(strict_types=1);

namespace Rabbit\Observer\Observers;

use Rabbit\Base\Core\Context;
use Rabbit\Observer\ObsFuncInterface;

class ObsRunTime implements ObsFuncInterface
{
    public function beforeHook(string $joinpoint, array $args, mixed $ret): void
    {
        Context::set('aop-' . explode('@', $joinpoint)[1], intval(microtime(true) * 1000000));
    }
    
    public function afterHook(string $joinpoint, array $args, mixed $ret): void
    {
        $total = intval(microtime(true) * 1000000) - Context::get('aop-' . explode('@', $joinpoint)[1]);
    }
}
