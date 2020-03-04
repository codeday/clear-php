<?php
namespace CodeDay\Clear\Services;
use Auth0\SDK\API\Management;
use \Auth0\SDK\Auth0;

class Auth {
    private static $auth0 = null;

    public static function boot() {
        self::$auth0 = new Auth0([
            'domain' => \config('auth0.domain'),
            'client_id' => \config('auth0.client_id'),
            'client_secret' => \config('auth0.secret'),
            'redirect_uri' => \url('/login'),
            'scope' => 'openid profile email https://codeday.xyz/username https://codeday.xyz/phone_number',
        ]);
    }

    public static function isLoggedIn() {
        return self::$auth0->getUser() !== null;
    }

    public static function getUserInfo($username = null) {
        if (!isset($username)) return self::$auth0->getUser();
        $mgmt = new Management(self::getManagementToken(), \config('auth0.domain'));
        $users = $mgmt->users->search(['q' => 'username:"' . $username . '"']);
        if (count($users) === 0) return null;
        $user = (object)$users[0];
        $user->permissions = $mgmt->users->getPermissions($user->user_id);
        $user->permissions = array_filter(
            $user->permissions,
            function($e) { return $e['resource_server_identifier'] === 'https://clear.codeday.org/'; }
        );
        $user->permissions = array_map(function($e) { return $e['permission_name']; }, $user->permissions);

        return $user;
    }

    public static function login() {
        self::$auth0->login();
    }

    public static function logout() {
        self::$auth0->logout();
        $return_to = 'http://' . $_SERVER['HTTP_HOST'];
        $logout_url = sprintf('http://%s/v2/logout?client_id=%s&returnTo=%s',
            \config('auth0.domain'),
            \config('auth0.client_id'),
            $return_to
        );
        header('Location: ' . $logout_url);
        die();
    }

    private static function getManagementToken() {
        if (!\Session::has('auth0_management_token')) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://" . \config('auth0.domain') . "/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=" . \config('auth0.management_id') . "&client_secret=" . \config('auth0.management_secret') . "&audience=https%3A%2F%2Fsrnd.auth0.com%2Fapi%2Fv2%2F&scope=read:users",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception($err);
            } else {
                \Session::set('auth0_management_token', \json_decode($response)->access_token);
            }
        }

        return \Session::get('auth0_management_token');
    }
}
Auth::boot();
