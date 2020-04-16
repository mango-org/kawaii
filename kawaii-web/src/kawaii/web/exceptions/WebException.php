<?php

namespace kawaii\web\exceptions;

use Exception;

/**
 * Class WebException
 * @package kawaii\web\exceptions
 */
class WebException extends Exception
{
    private int $httpCode;

    /**
     * WebException constructor.
     * @param int $httpCode
     * @param string $message
     */
    public function __construct(string $message, int $httpCode = 500)
    {
        parent::__construct($message);
        $this->httpCode = $httpCode;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
