<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class QueryController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('tools/query/index');
    }

    public function postIndex()
    {
        try {
            $results = \DB::select(\Input::get('query'));
        } catch (\Illuminate\Database\QueryException $ex) {
            return \View::make('tools/query/index', ['query' => \Input::get('query'), 'exception' => $ex->getMessage()]);
        }

        $data = [];
        $headings = [];

        if (count($results) > 0) {
            foreach ((array)$results[0] as $key=>$val) {
                $headings[] = $key;
            }

            foreach ($results as $row) {
                $dRow = [];
                foreach ($row as $cell) {
                    $dRow[] = $cell;
                }
                $data[] = $dRow;
            }
        }

        if (\Input::get('format') == 'csv') {
            $content = (
                implode(',', $headings)."\n".
                implode("\n", array_map(function($x) { return '"'.implode('","', $x).'"'; }, $data))
            );

            return (new \Illuminate\Http\Response($content, 200))
                ->header('Content-type', 'text/csv')
                ->header('Content-disposition', 'attachment;filename=clear-query-'.time().'.csv');
        } else {
            return \View::make('tools/query/index', ['results' => [
                'data' => $data,
                'headings' => $headings
            ], 'query' => \Input::get('query')]);
        }
    }

    public function getSaved()
    {
        return \View::make('tools/query/saved', ['queries' => Models\SavedQuery::get(), 'query' => \Input::get('query')]);
    }

    public function postSave()
    {
        return \View::make('tools/query/save', ['query' => \Input::get('query')]);
    }

    public function postDosave()
    {
        $query = new Models\SavedQuery;
        $query->name = \Input::get('name');
        $query->description = \Input::get('description');
        $query->sql = \Input::get('sql');
        $query->save();

        \Session::flash('status_message', 'Saved!');
        return \Redirect::to('/tools/query/saved');
    }

    public function postDelete()
    {
        Models\SavedQuery::where('id', '=', \Input::get('id'))->first()->delete();

        \Session::flash('status_message', 'Deleted!');
        return \Redirect::to('/tools/query/saved');
    }
}