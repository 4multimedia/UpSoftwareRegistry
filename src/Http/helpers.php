<?php

use Upsoftware\Registry\Classes\Registry;

if (! function_exists('registry')) {
    /**
     * Registry helper.
     */
    function registry(): Registry
    {
        return app('registry');
    }
}

