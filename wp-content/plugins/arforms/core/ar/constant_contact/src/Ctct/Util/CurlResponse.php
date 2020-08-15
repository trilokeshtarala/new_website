<?php

namespace Ctct\Util;


class CurlResponse
{
        
    public $body;
    public $error;
    public $info;
    
    public static function create($body, $info, $error = null)
    {
        $curl = new CurlResponse();
        
        $curl->body = $body;
        $curl->info = $info;
        $curl->error = $error;
        
        return $curl;
    }
}
