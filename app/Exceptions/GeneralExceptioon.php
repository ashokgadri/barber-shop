<?php

namespace App\Exceptions;

use Exception;

class GeneralExceptioon extends Exception
{
    public function render($request)
    {
        return response()->json(["message" => $this->getMessage()], 422);
    }
}
