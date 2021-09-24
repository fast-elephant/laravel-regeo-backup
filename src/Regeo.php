<?php


namespace FastElephant\LaravelRegeo;


class Regeo
{
    protected $context;

    protected $log = ['requests' => 0, 'responses' => []];

    public function __construct()
    {
        $this->context();
    }

    public function parse($lng, $lat)
    {
        $response = $this->qqmapParseResponse($this->qqmapRegeo($lng, $lat, config('regeo.qqmap.key')));

        if ($response['state'] != '10000') {
            $response = $this->amapParseResponse($this->amapRegeo($lng, $lat, config('regeo.amap.key')));
        }

        return $response;
    }

    public function bicycling($originLng, $originLat, $destinationLng, $destinationLat)
    {
        $response = $this->amapBicyclingResponse($this->amapBicycling($originLng . ',' . $originLat, $destinationLng . ',' . $destinationLat, config('regeo.amap.key')));

        if ($response['state'] != '10000') {
            $response = $this->qqmapBicyclingResponse($this->qqmapBicycling($originLat . ',' . $originLng, $destinationLat . ',' .$destinationLng, config('regeo.qqmap.key')));
        }

        return $response;
    }

    public function bicyclingWithKey($oriLng, $oriLat, $destLng, $destLat, $key, $map = 'qq')
    {
        if ('qq' == $map) {
            return $this->qqmapBicyclingResponse($this->qqmapBicycling($oriLat . ',' . $oriLng, $destLat . ',' . $destLng, $key));
        }
        if ('amap' == $map) {
            return $this->amapBicyclingResponse($this->amapBicycling($oriLng . ',' . $oriLat, $destLng . ',' . $destLat, $key));
        }
    }

    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $EARTH_RADIUS = 6378.137;

        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 10000) / 10000;

        return $s;
    }

    public function getLog()
    {
        return $this->log;
    }

    protected function context()
    {
        $this->context = stream_context_create([
            "ssl"  => [
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ],
            'http' => [
                'timeout' => config('regeo.timeout')
            ]
        ]);
    }

    protected function qqmapRegeo($lng, $lat, $key)
    {
        try {
            $url = 'https://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=' . $key;
            return file_get_contents($url, 0, $this->context);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function amapRegeo($lng, $lat, $key)
    {
        try {
            $url = 'https://restapi.amap.com/v3/geocode/regeo?key=' . $key . '&location=' . $lng . ',' . $lat . '&poitype=&radius=&extensions=base&batch=false&roadlevel=0';
            return file_get_contents($url, 0, $this->context);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function qqmapParseResponse(string $response)
    {
        $this->log($response);

        $response = json_decode($response, true);

        $infocode = array_get($response, 'status', 500);

        if ($infocode === 0) {
            return [
                'state'    => '10000',
                'province' => array_get($response, 'result.address_component.province', ''),
                'city'     => array_get($response, 'result.address_component.city', ''),
                'district' => array_get($response, 'result.address_component.district', ''),
                'street'   => array_get($response, 'result.address_component.street', ''),
                'number'   => array_get($response, 'result.address_component.street_number', ''),
            ];
        }

        return [
            'response' => $response,
            'state'    => $infocode,
        ];
    }

    protected function amapParseResponse(string $response)
    {
        $this->log($response);

        $response = json_decode($response, true);

        $infocode = array_get($response, 'infocode', '00000');

        if ($infocode === '10000') {
            return [
                'state'    => '10000',
                'province' => array_get($response, 'regeocode.addressComponent.province', ''),
                'city'     => array_get($response, 'regeocode.addressComponent.city', ''),
                'district' => array_get($response, 'regeocode.addressComponent.district', ''),
                'street'   => array_get($response, 'regeocode.addressComponent.streetNumber.street', ''),
                'number'   => array_get($response, 'regeocode.addressComponent.streetNumber.number', ''),
            ];
        }

        return [
            'state'    => $infocode,
        ];
    }

    protected function amapBicycling($origin, $destination, $key)
    {
        try {
            $url = 'https://restapi.amap.com/v4/direction/bicycling?key=' . $key . '&origin=' . $origin . '&destination=' . $destination;
            return file_get_contents($url, 0, $this->context);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function qqmapBicycling($from, $to, $key)
    {
        try {
            $url = 'https://apis.map.qq.com/ws/direction/v1/bicycling/?from=' . $from . '&to=' . $to . '&key=' . $key;
            return file_get_contents($url, 0, $this->context);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function amapBicyclingResponse(string $response)
    {
        $this->log($response);

        $response = json_decode($response, true);

        $infocode = array_get($response, 'errcode', 30000);

        if ($infocode === 0) {
            return [
                'state'    => '10000',
                'distance' => array_get($response, 'data.paths.0.distance', 0),
                'duration' => array_get($response, 'data.paths.0.duration', 0),
            ];
        }

        return [
            'response' => $response,
            'state'    => $infocode,
        ];
    }

    protected function qqmapBicyclingResponse(string $response)
    {
        $this->log($response);

        $response = json_decode($response, true);

        $infocode = array_get($response, 'status', 500);

        if ($infocode === 0) {
            return [
                'state'    => '10000',
                'distance' => array_get($response, 'result.routes.0.distance', 0),
                'duration' => array_get($response, 'result.routes.0.duration', 0) * 60,
            ];
        }

        return [
            'response' => $response,
            'state'    => $infocode,
        ];
    }

    protected function log($response)
    {
        $this->log['responses'][] = $response;
        $this->log['requests']    += 1;
    }

    private function rad($d)
    {
        return $d * M_PI / 180.0;
    }
}
