<?php namespace Tests;

use Jttp\Response;

class CustomResponse extends Response
{
    public function customMethod()
    {
        return "ok";
    }
}
