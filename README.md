# laravel-regeo

## install
```
composer require fast-elephant/laravel-regeo
```

## usage
publish
```
php artisan vendor:publish --provider="FastElephant\LaravelRegeo\RegeoServiceProvider"
```
config
```
return [

    'timeout' => 1, // request timeout

    'qqmap' => [ // qq map key
        'key' => ''
    ],

    'amap' => [ // amap key
        'key' => ''
    ],
];
```
usage
```
use FastElephant\LaravelRegeo\Regeo;

$regeo = new Regeo();
$result = $regeo->parse('117.177210739', '23.709481353');

// bicyling
// return ["state" => "10000","distance" => 310,"duration" => 416]
$result = $regeo->bicycling('116.307535', '39.984042', '116.325238', '39.980869');
```