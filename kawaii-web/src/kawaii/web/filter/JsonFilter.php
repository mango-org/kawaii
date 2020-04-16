<?php

namespace kawaii\web\filter;

use ReflectionClass;
use php\format\ProcessorException;
use php\http\HttpServerRequest;
use php\http\HttpServerResponse;
use php\lib\str;

class JsonFilter extends Filter
{
    /**
     * @inheritDoc
     * @throws ProcessorException
     */
    public function filter(ReflectionClass $class, HttpServerRequest $request, HttpServerResponse $response, $data): bool
    {
        if (is_array($data)) {
            $response->contentType("application/json");
            $response->write(str::formatAs($data, "json"));

            return true;
        }

        return false;
    }
}
