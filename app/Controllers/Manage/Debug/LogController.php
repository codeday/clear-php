<?php
namespace CodeDay\Clear\Controllers\Manage\Debug;

use \CodeDay\Clear\Models;

class LogController extends \Controller {

    public function getIndex()
    {
        return \View::make('debug/log', ['log' => self::tail(storage_path().'/logs/laravel.log', 100)]);
    }

    private static function tail($filename, $lines, $revers = false)
    {
        $offset = -1;
        $c = '';
        $read = '';
        $i = 0;
        $fp = @fopen($filename, "r");
        while( $lines && fseek($fp, $offset, SEEK_END) >= 0 ) {
            $c = fgetc($fp);
            if($c == "\n" || $c == "\r"){
                $lines--;
                if( $revers ){
                    $read[$i] = strrev($read[$i]);
                    $i++;
                }
            }
            if( $revers ) $read[$i] .= $c;
            else $read .= $c;
            $offset--;
        }
        fclose ($fp);
        if( $revers ){
            if($read[$i] == "\n" || $read[$i] == "\r")
                array_pop($read);
            else $read[$i] = strrev($read[$i]);
            return implode('',$read);
        }
        return strrev(rtrim($read,"\n\r"));
    }
} 