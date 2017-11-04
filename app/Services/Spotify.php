<?php
namespace CodeDay\Clear\Services;

/**
 * Supports some part of the Spotify API.
 *
 * @package     CodeDay\Clear\Services
 * @author      TJ Horner <tjhorner@srnd.org>
 * @copyright   (c) 2014-2017 srnd.org
 * @license     Perl Artistic License 2.0
 */
class Spotify {
  protected static $client;

  public static function GetOauthUri($state = "", $scope = [ ]) {
    return "https://accounts.spotify.com/authorize?" . http_build_query([
      "client_id" => \Config::get('spotify.client_id'),
      "response_type" => "code",
      "redirect_uri" => \Config::get('app.url') . "api/spotify/oauth",
      "state" => $state,
      "scope" => join(" ", $scope)
    ]);
  }

  public static function PostAsServer($url, $payload)
  {
    $opts = ['http' => ['method'  => 'POST']];

    $opts['http']['header'] = [
      'Content-type: application/x-www-form-urlencoded',
      'Authorization: Basic ' . base64_encode(\Config::get('spotify.client_id') . ':' . \Config::get('spotify.client_secret'))
    ];

    $opts['http']['content'] = http_build_query($payload);

    $context = stream_context_create($opts);
    return @file_get_contents($url, false, $context);
  }

  public static function RefreshAccessToken($refresh_token) {
    return self::PostAsServer("https://accounts.spotify.com/api/token", [
      "grant_type" => "refresh_token",
      "refresh_token" => $refresh_token
    ]);
  }

  public static function GetAsUser($url, $access_token)
  {
    $opts = ['http' => ['method'  => 'GET']];

    $opts['http']['header'] = [
      'Authorization: Bearer ' . $access_token
    ];

    $context = stream_context_create($opts);
    return @file_get_contents($url, false, $context);
  }

  public static function GetUser($access_token) {
    return self::GetAsUser("https://api.spotify.com/v1/me", $access_token);
  }

  public static function ExchangeCode($code) {
    return self::PostAsServer("https://accounts.spotify.com/api/token", [
      "grant_type" => "authorization_code",
      "code" => $code,
      "redirect_uri" => \Config::get('app.url') . "api/spotify/oauth"
    ]);
  }

  public static function GetUserNowPlaying($access_token) {
    return self::GetAsUser("https://api.spotify.com/v1/me/player/currently-playing", $access_token);
  }
}