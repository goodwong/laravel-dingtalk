<?php

namespace Goodwong\LaravelDingtalk\Exceptions;

use Exception as Base;

class Exception extends Base
{
    /**
     * 初始化
     *
     * @param  string    $message
     * @param  int       $code
     * @param  Exception $previous
     * @return Exception
     */
    public function __construct($message = "", $code = 0, Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->message = "{$code}: {$message}";
    }
}
