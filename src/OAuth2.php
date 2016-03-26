<?php
namespace LianYue\BaiduApi;


// http://developer.baidu.com/wiki/index.php?title=docs/oauth/rest/file_data_apis_list
// http://developer.baidu.com/console#app/project
// http://developer.baidu.com/console#app/create
class OAuth2
{

    protected $baseUri = 'https://openapi.baidu.com';

    protected $clientId;

    protected $clientSecret;

    protected $state;

    protected $accessToken;

    protected $redirectUri;

    protected $requestOptions = array();

    public function __construct($clientId, $clientSecret, array $accessToken = null, array $requestOptions = array())
    {
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
        $this->setAccessToken($accessToken);
        $this->setRequestOptions($requestOptions);
    }


    public function getClientId()
    {
        return $this->clientId;
    }


    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }


    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }



    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = (string) $redirectUri;
        return $this;
    }

    public function getState()
    {
        if (!$this->state) {
            $this->state = md5(uniqid(mt_rand(), true));
        }
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = (string) $state;
        return $this;
    }

    public function setRequestOptions(array $requestOptions = array())
    {
        $this->requestOptions =  $requestOptions;
        return $this;
    }


    public function getAccessToken(array $params = null)
    {
        if ($this->accessToken === null) {
            //  自动获取 access_token
            if ($params === null) {
                $params = $_GET;
            }
            if (empty($params['code'])) {
                throw new InvalidArgumentException('Code parameter is empty');
            }
            if (empty($params['state']) || $params['state'] !== $this->getState()) {
                throw new InvalidArgumentException('State parameter error (CSRF)');
            }
            $request = $this->request('GET', 'oauth/2.0/token', array(
                'grant_type' => empty($params['grant_type']) ? 'authorization_code' : $params['grant_type'],
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'code' => $params['code'],
                'redirect_uri' => empty($params['redirect_uri']) ? $this->getRedirectUri() : $params['redirect_uri'],
            ));
            $this->accessToken = $request->response()->getJson(true);
        }
        return $this->accessToken;
    }

    public function setAccessToken(array $accessToken = null)
    {
        $this->accessToken = $accessToken;
        return $this;
    }


    public function getAuthorizeUri(array $params = array())
    {

        if (!empty($params['state'])) {
            $this->setState($params['state']);
        }

        if (!empty($params['redirect_uri'])) {
            $this->setRedirectUri($params['redirect_uri']);
        } else {
            $params['redirect_uri'] = $this->getRedirectUri();
            if (!$params['redirect_uri']) {
                throw new InvalidArgumentException('Not configuration redirect_uri');
            }
        }

        $params = array(
			'client_id' => $this->getClientId(),
			'state' => $this->getState(),
		) + $params + array(
            'response_type'	=> 'code',
        );

        if (!empty($params['scope']) && is_array($params['scope'])) {
            $params['scope'] = implode(',', $params['scope']);
        }
        return $this->getUri('oauth/2.0/authorize', $params);
    }

    public function getLogoutUri(array $params = array())
    {
        if (empty($params['access_token'])) {
            $accessToken = $this->getAccessToken();
            if (empty($accessToken['access_token'])) {
                throw new InvalidArgumentException('Not configuration access_token');
            }
            $params['access_token'] = $accessToken['access_token'];
        }
        if (empty($params['next'])) {
            if (empty($params['redirect_uri'])) {
                $params['next'] = $this->getRedirectUri();
                if (!$params['next']) {
                    throw new InvalidArgumentException('Not configuration redirect_uri');
                }
            } else {
                $params['next'] = (string) $params['redirect_uri'];
            }
        }
        unset($params['redirect_uri']);
        return $this->getUri('connect/2.0/logout', $params);
    }


    /**
    * http://developer.baidu.com/wiki/index.php?title=docs/oauth/Refresh_Token
    **/
    public function getAccessTokenByRefreshToken(array $params = array())
    {
        $params = array(
            'grant_type' => 'refresh_token',
			'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
		) + $params;
        if (empty($params['refresh_token'])) {
            $accessToken = $this->getAccessToken();
            if (empty($accessToken['refresh_token'])) {
                throw new InvalidArgumentException('Not configuration refresh_token');
            }
            $params['refresh_token'] = $accessToken['refresh_token'];
        }

        if (!empty($params['scope']) && is_array($params['scope'])) {
            $params['scope'] = implode(',', $params['scope']);
        }
        $request = $this->request('GET', 'oauth/2.0/token', $params);
        $this->accessToken = $request->response()->getJson(true);
        return $this->accessToken;
    }


    /**
    *   http://developer.baidu.com/wiki/index.php?title=docs/oauth/client
    **/
    public function getAccessTokenByClientCredentials(array $params = array())
    {
        $params = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->getClientId(),
            'client_secret'	=> $this->getClientSecret(),
        ) + $params;
        $request = $this->request('GET', 'oauth/2.0/token', $params);
        return $request->response()->getJson(true);
    }

    /**
    * http://developer.baidu.com/wiki/index.php?title=docs/oauth/baidu_developer
    *
    **/
    public function getAccessTokenByDeveloperCredentials(array $params = array())
    {
        $params = array(
            'grant_type' => 'developer_credentials',
            'client_id' => $this->getClientId(),
            'client_secret'	=> $this->getClientSecret(),
        ) + $params;
        $request = $this->request('GET', 'oauth/2.0/token', $params);
        return $request->response()->getJson(true);
    }


    public function getUserInfo()
    {
        return $this->api('GET', 'rest/2.0/passport/users/getInfo')->response();
    }

    public function getUri($path, array $params = array())
    {
        if (substr($path, 0, 7) === 'http://' || substr($path, 0, 8) === 'https://') {
            $uri = $path;
        } else {
            $uri = $this->baseUri .'/' . ltrim($path, '/');
        }
        if ($params) {
            $uri .= '?' . http_build_query($params, null, '&');
        }
        return $uri;
    }

    public function request($method, $path, array $params = array(), array $headers = array(), $body = null, array $options = array())
    {
        $request = new Request($method, $this->getUri($path, $params), $headers, $body, $options + $this->requestOptions + array(CURLOPT_USERAGENT => 'OAuth/2.0 (LianYue; http://lianyue.org, https://github.com/lian-yue/baidu-api)'));
        return  $request->setResponseCallback(function(Response $response) {

            $json = $response->getJson();

            if ($response->getStatusCode() >= 400) {
                if (!empty($json->error_description)) {
                    $error = $json->error_description;
                } elseif (!empty($json->error)) {
                    $error = $json->error;
                } else {
                    $error = sprintf('HTTP status code %d', $response->getStatusCode());
                }
                throw new ResponseException($error, $response->getStatusCode());
            } elseif (!empty($json->error_code)) {
                throw new ResponseException(empty($error->error_msg) ? sprintf('Error code %s', (string) $error->error_code) : $error->error_msg, $json->error_code);
            }
            return $response;
        });
    }


    public function api($method, $path, array $params = array(), array $headers = array(), $body = null) {
        if (empty($params['access_token'])) {
            $accessToken = $this->getAccessToken();
            if (empty($accessToken['access_token'])) {
                throw new InvalidArgumentException('Not configuration access_token');
            }
            $params['access_token'] = $accessToken['access_token'];
        }
        return $this->request($method, $path, $params, $headers, $body);
    }
}
