<?php

namespace Wpjscc\Request;

use Wpjscc\React\Limiter\RateLimiter;
use Evenement\EventEmitter;
use function React\Async\async;
use function React\Async\await;

class Limiter extends EventEmitter
{
    private $limiter;

    private $isQueue;

    private $queues;

    private $runing;


    public function __construct(int $tokensPerInterval = 1, int | string $interval = 1, bool $isQueue = false)
    {
        $this->limiter = new RateLimiter($tokensPerInterval, $interval);
        $this->isQueue = $isQueue;
        $this->queues = new \SplQueue();
    }

    public function addHandle($key, callable $handler)
    {
        $this->queues->enqueue([$key, $handler]);
        $this->run();
    }

    private function run()
    {
        if ($this->runing || $this->queues->isEmpty()) {
            return;
        }
        $this->runing = true;
        async(function () {
            $queue = $this->queues->dequeue();
            $key = $queue[0];
            $handler = $queue[1];
            await($this->limiter->removeTokens(1));
            $handler()->then(function ($data) use ($key) {
                $this->emit('data', [$key, $data]);
            }, function ($error) use ($key) {
                $this->emit('error', [$key, $error]);
            })->finally(function () {
                if ($this->isQueue) {
                    $this->nextRun();
                }
            });

            if (!$this->isQueue) {
                $this->nextRun();
            }
        })();
    }

    private function nextRun()
    {
        $this->runing = false;
        $this->run();
    }
}
