<?php
namespace s5\Endpoints;

/**
 * Single-sign-on with s5
 *
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes.
 *
 */
class OAuth
{
    public $API;

    public function RequestCode($return, $scope = [])
    {
        $state = mt_rand(0,mt_getrandmax());
        $scope = implode(',', $scope);
        $query_string = http_build_query([
            'state' => $state,
            'scope' => $scope,
            'return' => $return
        ]);
        return (object)[
            'URL' => \s5\API::$OAuthBase.'/'.$this->API->Token.'?'.$query_string,
            'State' => $state
        ];
    }

    public function RequestAccessToken($code = null, $state = null, $expectedState = null)
    {
        if (!isset($code)) {
            $code = $_GET['code'];
        }

        if (isset($state) && isset($expectedState)) {
            if ($state != $expectedState) {
                throw new \s5\Exceptions\InvalidState('Expected '.$expectedState.', actual '.$state.'.');
            }
        }

        try {
            return $this->API->GET('oauth/exchange', ['code' => $code]);
        } catch (\s5\Exceptions\Server $ex) {
            throw new \s5\Exceptions\InvalidCode();
        }
    }
}