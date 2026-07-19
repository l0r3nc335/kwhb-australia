<?php

namespace App\Helpers;


class DataResponse
{
    public $message = "";
    public $data;
    public $error = false;
    public $tags = 0;
    public $errorCodes = [];
    public static function instance()
    {
        return new DataResponse();
    }

    public function ErrorResponse($e)
    {
        $this->message = $e->getMessage();
        $this->error = true;
        $this->errorCodes = [500];
    }
}
