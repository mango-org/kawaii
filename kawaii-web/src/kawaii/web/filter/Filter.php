<?php

namespace kawaii\web\filter;

use ReflectionClass;
use kawaii\core\Component;
use php\http\HttpServerRequest;
use php\http\HttpServerResponse;

/**
 * Class Filter
 * @package kawaii\web
 */
abstract class Filter extends Component
{
    /**
     * @param ReflectionClass $class
     * @param HttpServerRequest $request
     * @param HttpServerResponse $response
     * @param $data
     * @return bool
     */
    abstract public function filter(ReflectionClass $class, HttpServerRequest $request, HttpServerResponse $response, $data): bool;
}
