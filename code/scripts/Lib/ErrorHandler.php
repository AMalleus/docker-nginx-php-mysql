<?php

namespace Lib;

use Slim\Http\Request;
use Slim\Http\Response;

class ErrorHandler
{

    public function __invoke(Request $request, Response $response, \Exception $exception)
    {

    }
}