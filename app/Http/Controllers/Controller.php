<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function addQuotesWhenNotNull($value)
    {
        if ($value != "null")
            $value = "'" . $value . "'";

        return $value;
    }
}
