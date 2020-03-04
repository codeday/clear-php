<?php

\Route::get('/login', function() {
  if (\CodeDay\Clear\Services\Auth::isLoggedIn())
    return \redirect('/');

  \CodeDay\Clear\Services\Auth::login();
});

\Route::get('/logout', function() {
  \CodeDay\Clear\Services\Auth::logout();
});
