<?php
namespace LianYue\BaiduApi;
class Map
{

    const URI_BASE = 'http://api.map.baidu.com';

    protected static $callbacks = array(
        'getPlaceSearch' => array(
            'path' => '/place/v2/search',
            'params' => array(
                'scope' => 1,
            ),
        ),
        'getPlaceDetail' => array(
            'path' => '/place/v2/detail',
            'params' => array(
                'scope' => 1,
            ),
        ),
        'getPlaceSuggestion' => array(
            'path' => '/place/v2/suggestion',
            'params' => array(
                'scope' => 1,
            ),
        ),
        'getGeocoder' => array(
            'path' => '/geocoder/v2/',
            'params' => array(
            ),
        ),
        'getDirection' => array(
            'path' => '/direction/v1',
            'params' => array(
                'mode' => 'driving',
                'coord_type' => 'bd09ll',
            ),
        ),
        'getDirectionRoutematrix' => array(
            'path' => '/direction/v1/routematrix',
            'params' => array(
                'mode' => 'driving',
                'coord_type' => 'bd09ll',
            ),
        ),
        'getLocationIp' => array(
            'path' => '/location/ip',
            'params' => array(
                'mode' => 'driving',
                'coord_type' => 'bd09ll',
            ),
        ),
        'getGeoConv' => array(
            'path' => '/geoconv/v1/',
            'params' => array(

            ),
        ),

        'getStaticImage' => array(
            'path' => '/staticimage/v2',
            'params' => array(
                'width' =>300,
                'height' =>200,
            ),
        ),
    );

    protected $requestOptions = array();

    protected $ak;

    protected $sk;

    public function __construct($ak, $sk = fasle, array $requestOptions = array())
    {
        $this->setAk($ak);
        $this->setSk($sk);
    }


    public function getAk()
    {
        return $this->ak;
    }


    public function setAk($ak)
    {
        $this->ak = $ak;
        return $this;
    }

    public function getSk()
    {
        return $this->sk;
    }


    public function setSk($sk)
    {
        $this->sk = $sk;
        return $this;
    }

    public function __call($name, $params) {
        if (empty(self::$callbacks[$name])) {
            throw new \BadMethodCallException(sprintf('%s method does not exist', $name));
        }
        if ($params) {
            $params = $params[0];
        }
        if (!empty(self::$callbacks[$name]['params'])) {
            $params += self::$callbacks[$name]['params'];
        }

        return $this->get(self::$callbacks[$name]['path'], $params);
    }


    public function getUri($path, array $params = array())
    {
        $params['ak'] = $this->getAk();
        if ($sk = $this->getSk()) {
            $params['timestamp'] = time() . mt_rand(000,999);
        }

        ksort($params);
        $uri = '/'. ltrim($path, '/');

        if ($params) {
            $uri .= '?' . http_build_query($params + ['output' => 'json'], null, '&');
        }
        if ($sk) {
            $sn = md5(urlencode($uri . $sk));
            $uri .= '&sn=' . $sn;
        }
        return self::URI_BASE . $uri;
    }


    public function request($method, $path, array $params = array(), array $headers = array(), $body = null, array $options = array())
    {
        $request = new Request($method, $this->getUri($path, $params), $headers, $body, $options + $this->requestOptions + [CURLOPT_USERAGENT => 'Map/2.0 (LianYue; http://lianyue.org, https://github.com/lian-yue/baidu-api)']);
        return  $request->setResponseCallback(function(Response $response) {
            if ($response->getStatusCode() >= 400) {
                throw new ResponseException(sprintf('HTTP status code %d', $response->getStatusCode()), $response->getStatusCode());
            }
            $json = $response->getJson();
            if (!$json) {
                throw new ResponseException('Response is empty');
            }
            if (!empty($json->status)) {
                throw new ResponseException(empty($json->message) ? $json->status : $json->message, $json->status);
            }
            return $response;
        });
    }


    public function get($path, array $params = array())
    {
        return $this->request('GET', $path, $params, array(), null);
    }

    public function post($path, array $params = array(), $body = null)
    {
        return $this->request('POST', $path, $params, array(), $body);
    }

    public function put($path, array $params = array(), $body = null)
    {
        return $this->request('PUT', $path, $params, array(), $body);
    }
}
