# reactphp-request-limiter

## install

```
composer require  reactphp-request-limiter -vvv
```

## Usage

```php

    require __DIR__ . '/vendor/autoload.php';

    use Wpjscc\Request\Limiter;

    // 10 request per second
    $limiter = new Limiter(10, 1000);
    $start = microtime(true);

    $limiter->on('data', function ($key, $data) {
       echo $key . ' is completed ' . PHP_EOL;
    });


    for ($i=0; $i < 100; $i++) {
        $limiter->addHandle($i, function () {
            return \React\Promise\resolve(true);
        });
    }
```

## tests

```
./vendor/bin/phpunit tests
```