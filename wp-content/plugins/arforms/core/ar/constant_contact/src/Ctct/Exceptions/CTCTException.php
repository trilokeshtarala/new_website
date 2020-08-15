<?php
namespace Ctct\Exceptions;

use Exception;


class CtctException extends Exception
{
    
    private $errors;
    private $curlInfo;
    
    
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }

    public function setCurlInfo(array $info)
    {
        $this->curlInfo = $info;
    }

    public function getCurlInfo()
    {
        return $this->curlInfo;
    }
}
