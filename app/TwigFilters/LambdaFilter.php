<?php
namespace CodeDay\Clear\TwigFilters;

class LambdaFilter extends \Twig_Extension {
    public function getName() {
        return 'lambda_filter';
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('call', [$this, 'doCall'])
        );
    }

    public function doCall() {
        $arguments = func_get_args();
        $callable = array_shift($arguments);
        if(!is_callable($callable)) {
            throw new InvalidArgumentException();
        }
        return call_user_func_array($callable, $arguments);
    }
}