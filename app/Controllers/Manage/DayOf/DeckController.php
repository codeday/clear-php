<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;

class DeckController extends \Controller {

    public function getIndex()
    {
        return \View::make('dayof/deck/index');
    }

    public function getSlides()
    {
        return \View::make('dayof/deck/slides');
    }

    public function getNotes()
    {
        return \View::make('dayof/deck/notes');
    }
}