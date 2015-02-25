# s5 for PHP

This library simplifies working with s5 with PHP.

# Sample Use

To get started, initialize the s5 instance.

    $s5 = new \s5\API;
    $s5->Token = YOUR_APP_TOKEN;
    $s5->Secret = YOUR_APP_SECRET;

You can get these properties from the Apps section of s5 once logged in.

If you have an access_token for a user you'd like to use, you can set that too:

    $s5->AccessToken = USER_ACCESS_TOKEN;

You can pass any of these parameters in the constructor for simplicity:

    $s5 = new \s5\API(YOUR_APP_TOKEN, YOUR_APP_SECRET, USER_ACCESS_TOKEN);

## Logging In

The simplest way to handle login is to call the `RequireLogin` method.

    $s5->RequireLogin();

This will automatically handle OAuth, store the access token in a session variable, and set the proper access_token on
the `s5\API` instance every time it's loaded.

`RequireLogin` takes one parameter, `scope`, which is an array of the requested permissions for this auth. To get
address information for the logged in user, for example, you should call `RequireLogin` with `['extended']`.

Note that for this to work, you need to create an instance of the `\s5\API` with the proper app token and secret upon a
GET request to each page you call `RequireLogin` on, so it can process the last leg of the OAuth dance and set the
AccessToken in the session variable. This will usually not be a problem, but if you're having issues, that's a good
place to start.

## Accessing Data

To get the current user's data, with an AccessToken set, call:

    $s5->User->me();

This will return an object with the user's properties.

To get another user's data, call:

    $s5->User->get(USERNAME);
