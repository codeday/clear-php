<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;

class DeckController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \Redirect::to('https://present.codeday.org/');
    }

    public function getSlides()
    {
        return \Redirect::to('https://present.codeday.org/');
    }

    public function getNotes()
    {
        return \Redirect::to('https://present.codeday.org/');
    }
}
