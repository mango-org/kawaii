<?php

namespace kawaii\web\exceptions;

use Exception;

/**
 * Class RedirectException
 * @package kawaii\web\exceptions
 */
class RedirectException extends Exception
{
    protected string $location;

    /**
     * RedirectException constructor.
     * @param string $location
     */
    public function __construct(string $location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }
}
