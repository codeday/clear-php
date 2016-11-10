<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;

class DeckController extends \CodeDay\Clear\Http\Controller {

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