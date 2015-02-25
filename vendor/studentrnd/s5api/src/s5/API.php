<?php
namespace s5;

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'require.php');

/**
 *
 *
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes.
 *
 */
class API
{
    public static $APIBase = 'https://s5.studentrnd.org/api';
    public static $OAuthBase = 'https://s5.studentrnd.org/oauth';

    public $Token;
    public $Secret;
    public $AccessToken;

    public function __construct($token = null, $secret = null, $accessToken = null)
    {
        $this->Token = $token;
        $this->Secret = $secret;
        $this->AccessToken = $accessToken;

        $this->TryDoLogin();
    }

    const SessionScopeStore = '_s5_scope';
    const SessionRequestedScopeStore = '_s5_scope_requested';
    const SessionAccessTokenStore = '_s5_access_token';
    const SessionStateStore = '_s5_state';
    const UriDoLoginKey = '_s5_do';
    const UriDoLoginValue = 's5-login';
    const UriCodeKey = 'code';
    const UriStateKey = 'state';
    public function RequireLogin($scope = [])
    {
        $this->TryDoLogin();

        $accessTokenSet = array_key_exists(self::SessionAccessTokenStore, $_SESSION);
        $scopeSet = array_key_exists(self::SessionScopeStore, $_SESSION);
        $scopeMatch = true;

        if ($scopeSet) {
            foreach ($scope as $requestedScope) {
                if (!in_array($requestedScope, $_SESSION[self::SessionScopeStore])) {
                    $scopeMatch = false;
                }
            }
        }


        $returnURL = self::getFullURL();
        $returnSep = strpos($returnURL, '?') === false ? '?' : '&';
        $returnURL .= $returnSep.http_build_query([
                                    self::UriDoLoginKey => self::UriDoLoginValue,

            ]);

        if (!($accessTokenSet && $scopeSet && $scopeMatch))
        {
            $requestURL = $this->OAuth->RequestCode($returnURL, $scope);
            $prevScope = array_key_exists(self::SessionScopeStore, $_SESSION)
                                ? $_SESSION[self::SessionScopeStore] : [];


            $_SESSION[self::SessionStateStore] = $requestURL->State;
            $_SESSION[self::SessionRequestedScopeStore] = array_merge($prevScope, $scope);


            header('Location: '.$requestURL->URL);
            exit;
        }

        $this->AccessToken = $_SESSION[self::SessionAccessTokenStore];

        return true;
    }

    private $hasTriedLogin = false;
    private function TryDoLogin()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!$this->hasTriedLogin && array_key_exists(self::UriDoLoginKey, $_GET) && $_GET[self::UriDoLoginKey] === self::UriDoLoginValue) {
            $this->hasTriedLogin = true;
            $code = $_GET[self::UriCodeKey];
            $state = $_GET[self::UriStateKey];
            $expectedState = isset($_SESSION[self::SessionStateStore])
                                ? $_SESSION[self::SessionStateStore] : null;

            try {
                $accessToken = $this->OAuth->RequestAccessToken($code, $state, $expectedState);

                // Update the session object:
                // Access Token
                $_SESSION[self::SessionAccessTokenStore] = $accessToken;

                // Requested scopes
                if (array_key_exists(self::SessionRequestedScopeStore, $_SESSION)) {
                    $_SESSION[self::SessionScopeStore] = $_SESSION[self::SessionRequestedScopeStore];
                }

                // Remove old state
                unset($_SESSION[self::SessionStateStore]);
            } catch (\s5\Exceptions\InvalidCode $ex) { }

            unset($_SESSION[self::SessionStateStore]);
        }
    }

    private static function getFullURL()
    {
        $s = $_SERVER;
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');

        if (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER)) {
            $ssl = $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
        }

        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];

        $uri = $s['REQUEST_URI'];
        $path = $uri;
        if (false !== ($qs_index = strpos($uri, '?'))) {
            $path = substr($uri, 0, $qs_index);
        }

        $getdata = $_GET;

        foreach ([self::UriStateKey, self::UriCodeKey, self::UriDoLoginKey] as $key) {
            if (isset($getdata[$key])) {
                unset($getdata[$key]);
            }
        }

        $qs = http_build_query($getdata);

        return $protocol . '://' . $host . $path. (strlen($qs) > 0 ? '?'.$qs : '');
    }

    public function GET($endpoint, $params = [])
    {
        return $this->Query('GET', $endpoint, $params);
    }

    public function POST($endpoint, $params = [])
    {
        return $this->Query('POST', $endpoint, $params);
    }

    public function PATCH($endpoint, $params = [])
    {
        return $this->Query('PATCH', $endpoint, $params);
    }

    public function PUT($endpoint, $params = [])
    {
        return $this->Query('PUT', $endpoint, $params);
    }

    public function DELETE($endpoint, $params = [])
    {
        return $this->Query('DELETE', $endpoint, $params);
    }


    public function Query($method, $endpoint, $params = [])
    {
        $url = self::$APIBase.'/'.$endpoint;

        if (isset($this->Secret)) {
            $params['secret'] = $this->Secret;
        }
        if (isset($this->AccessToken)) {
            $params['access_token'] = $this->AccessToken;
        }

        $method = strtoupper($method);
        if ($method == 'GET' && count($params) > 0) {
            $sep = strstr($url, '?') ? '&' : '?';
            $url .= $sep.http_build_query($params);
            $params = [];
        }

        $opts = ['http' => ['method'  => $method]];

        if (count($params) > 0) {
            $opts['http']['header'] = 'Content-type: application/x-www-form-urlencoded';
            $opts['http']['content'] = http_build_query($params);
        }

        $context  = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            throw new \Exception("Unknown error fetching ".$url);
        }

        list($version, $code, $message) = preg_split('/ +/', $http_response_header[0]);
        if ($code !== '200') {
            throw new \s5\Exceptions\Server($code.' '.$message);
        }

        if ($this->getHeader($http_response_header, 'Content-type') === 'application/json') {
            return json_decode($result);
        } else {
            return $result;
        }
    }

    private function getHeader($headers, $key)
    {
        foreach ($headers as $header) {
            if (false === strpos($header, ':')) {
                continue;
            }

            list($hkey, $hvalue) = explode(':', $header);
            $hkey = trim($hkey);
            $hvalue = trim($hvalue);

            if (strtoupper($key) == strtoupper($hkey)) {
                return $hvalue;
            }
        }

        return null;
    }

    private $classCache = [];
    public function __get($key)
    {
        if (!isset($this->classCache[$key])) {
            $file = implode(DIRECTORY_SEPARATOR, [dirname(__FILE__), 'Endpoints', $key.'.php']);
            if (file_exists($file)) {
                require_once($file);
                $className = '\\s5\\Endpoints\\'.$key;
                $class = new $className();
                $class->API = $this;
                $this->classCache[$key] = $class;
            } else {
                return null;
            }
        }

        return $this->classCache[$key];
    }
} 
