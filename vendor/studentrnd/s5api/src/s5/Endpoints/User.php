<?php
namespace s5\Endpoints;

/**
 * Deals with user things
 *
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes.
 *
 */
class User
{
    public $API;

    public function get($username)
    {
        return $this->API->GET('user', ['username' => $username]);
    }

    public function me()
    {
        if (!isset($this->API->AccessToken)) {
            throw new \Exception('Requires a user token.');
        }

        return $this->API->GET('user/me');
    }
}