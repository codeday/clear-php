<?php
namespace CodeDay\Clear\TwigFilters;

class VarDumpFilter extends \Twig_Extension {
    public function getName() {
        return 'var_dump_filter';
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('var_dump', [$this, 'doDump'])
        );
    }

    public function doDump($obj) {
        var_dump($obj);
    }
}