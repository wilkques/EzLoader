<?php

if (!function_exists('loadPHP')) {
    /**
     * requrie mapping php
     * 
     * @return string
     */
    function loadPHP()
    {
        require_once dirname(__DIR__) . "/src/Loader.php";

        \Wilkques\EzLoader\Loader::autoRegister();
    }
}

if (!function_exists('ve')) {
    /**
     * var_export
     */
    function ve()
    {
        array_map(function ($item) {
            var_export($item);
            echo "\r\n";
        }, func_get_args());
    }
}

if (!function_exists('ved')) {
    /**
     * var_export
     */
    function ved()
    {
        call_user_func_array('ve', func_get_args());

        die;
    }
}

?>