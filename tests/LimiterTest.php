<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Wpjscc\Request\Limiter;
use function React\Async\await;

final class LimiterTest extends TestCase
{
    public function testLimiter()
    {
        $limiter = new Limiter(10, 1000);
        $start = microtime(true);

        $deferred = new \React\Promise\Deferred();
        $limiter->on('data', function ($key, $data) use ($deferred) {
            if ($key == 10) {
                $deferred->resolve(true);
            }
        });

        for ($i=0; $i < 11; $i++) {
            $limiter->addHandle($i, function () {
                return \React\Promise\resolve(true);
            });
        }

        await($deferred->promise());
        $end = microtime(true);
        $diff = $end - $start;
        // 11个 大约 1 秒
        $this->assertTrue($diff > 1);
        // 11个 小于 1.01 秒
        $this->assertTrue($diff < 1.010);        
    }
}