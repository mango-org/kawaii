<?php

namespace kawaii\web\filter;

use ReflectionClass;
use php\http\HttpServerRequest;
use php\http\HttpServerResponse;

class RawStringFilter extends Filter
{
    /**
     * @inheritDoc
     */
    public function filter(ReflectionClass $class, HttpServerRequest $request, HttpServerResponse $response, $data): bool
    {
        if (is_string($data)) {
            $response->write($data);
        }

        return false;
    }
}
