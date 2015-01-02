<?php
namespace CodeDay\Clear\TwigFilters;

class JsonDecodeFilter extends \Twig_Extension {
    public function getName() {
        return 'json_decode_filter';
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('json_decode', [$this, 'doDecode'])
        );
    }

    public function doDecode($obj) {
        return json_decode($obj);
    }
}