<?php

namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;

class ImageController extends \CodeDay\Clear\Http\Controller
{
    private function getClass()
    {
        $class = \Route::input('class');
        $id = \Route::input('id');

        switch ($class) {
            case "sponsor":
                return Models\Batch\Event\Sponsor::find($id);
            case "region":
                return Models\Region::find($id);
            default:
                \App::abort(404);
        }
    }

    private function getPhoto()
    {
        $class = \Route::input('class');

        switch ($class) {
            case "sponsor":
                return $this->getClass()->logo;
            case "region":
                return $this->getClass()->image;
            default:
                \App::abort(404);
        }
    }

    private function getUpdatedAt()
    {
        $class = \Route::input('class');

        switch ($class) {
            default:
                return $this->getClass()->updated_at->timestamp;
        }
    }

    private function getResizeFunction()
    {
        $class = \Route::input('class');

        switch($class) {
            case "sponsor":
                return 'bound';
            default:
                return 'fill';
        }
    }

    public function redirectPhoto()
    {
        $size = \Route::input('imagesize');
        $class = \Route::input('class');
        $id = \Route::input('id');

        $response = \Response::make('', 302);
        $response->header('Content-Type', 'image/jpeg');
        $response->header('Cache-control', 'public,max-age=300,no-transform');
        $response->header('Expires', date('r', time() + 300));
        $response->header('Location', '/api/i/'.$class.'/'.$id.'_'.$size.'/'.$this->getUpdatedAt().'.jpg');

        return $response;
    }

    public function showPhoto()
    {
        $size = \Route::input('imagesize');
        if ($this->getPhoto() !== null) {
            $image = new Models\Image($this->getPhoto());
        } else {
            $image = new Models\Image(public_path().'/assets/img/default_'.\Route::input('class').'.png');
        }

        $size_x = $size_y = $size;
        if (strpos($size, ',') !== false) {
            list($size_x, $size_y) = explode(',', $size);
        }

        $image->backfill(255, 255, 255);

        $resizeFunction = $this->getResizeFunction();
        $image->$resizeFunction($size_x, $size_y);

        $response = \Response::make('', 200);
        // Images bigger than ~100x100px will cause PHP to flush the output buffer, so we need to send a header now
        // but images smaller than that won't cause any output buffering, so we need to return a response with the
        // proper header so it doesn't get overridden.
        //
        // This wouldn't be a problem if imagepng would return instead of echoing.
        header('Content-type: image/jpeg');
        header('Cache-control: public,max-age=604800,no-transform');
        $response->header('Content-Type', 'image/jpeg');
        $response->header('Cache-control', 'public,max-age=604800,no-transform');

        imagejpeg($image->getResource());

        return $response;
    }
}
