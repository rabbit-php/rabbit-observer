<?php

declare(strict_types=1);

namespace Rabbit\Observer;

use EasyAop;
use Rabbit\Base\Contract\InitInterface;
use Rabbit\DB\Redis\Redis;

class ObserverManager implements InitInterface
{
    private Redis $redis;
    const CHANNEL_ADD = 'observer:add';
    const CHANNEL_DEL = 'observer:del';

    private array $observers = [];

    public function __construct(private bool $local = true, private string $name = 'ext')
    {
        $this->redis = service('redis')->get($name);
    }

    public function init(): void
    {
        $pool = $this->redis->getPool();
        $redis = $pool->get();
        $pool->sub();
        rgo(fn () => $redis->subscribe([self::CHANNEL_ADD, self::CHANNEL_DEL], [$this, 'recv']));
    }

    public function recv(\Redis $redis, string $channel, string $msg): void
    {
        if ($channel === self::CHANNEL_ADD) {
            [$name, $class] = json_decode($msg, true);
            $this->add($name, create($class));
        } elseif ($channel === self::CHANNEL_DEL) {
            $this->del($msg);
        }
    }

    public function addObserver(string $name, string $func): void
    {
        $this->add($name, create($func));
        if ($this->local) {
            return;
        }
        $this->redis->publish(self::CHANNEL_ADD, json_encode([$name, $func]));
    }

    public function add(string $name, ObsFuncInterface $func): void
    {
        if (!($this->observers[$name] ?? false)) {
            $this->observers[$name] = 1;
            EasyAop::add_advice([
                "bhook@{$name}",
            ], [$func, 'beforeHook']);

            EasyAop::add_advice([
                "ahook@{$name}",
            ], [$func, 'afterHook']);
        }
    }

    public function delObserver(string $name): void
    {
        $this->del($name);
        if ($this->local) {
            return;
        }
        $this->redis->publish(self::CHANNEL_DEL, $name);
    }

    public function del(string $name): void
    {
        if (!($this->observers[$name] ?? false)) {
            return;
        }
        EasyAop::del_hook([
            "bhook@{$name}",
            "ahook@{$name}",
        ]);
        unset($this->observers[$name]);
    }

    public function updateObserver(string $name, string $func): void
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
