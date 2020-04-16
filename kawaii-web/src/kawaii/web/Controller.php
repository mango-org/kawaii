<?php

namespace kawaii\web;

use kawaii\core\Component;
use kawaii\utils\AnnotationsUtils;
use kawaii\utils\Logger;
use kawaii\web\exceptions\RedirectException;
use kawaii\web\exceptions\WebException;
use kawaii\web\filter\Filter;
use php\http\HttpRedirectHandler;
use php\http\HttpServerRequest;
use php\http\HttpServerResponse;
use php\lib\str;
use php\util\RegexException;
use ReflectionClass;
use ReflectionException;
use Throwable;

abstract class Controller extends Component
{
    /**
     * @param HttpServerRequest $request
     * @param HttpServerResponse $response
     * @throws ReflectionException
     * @throws RegexException
     */
    public function __invoke(HttpServerRequest $request, HttpServerResponse $response)
    {
        Logger::debug("New request to '{0}'", $request->path());

        $clazz = new ReflectionClass($this);
        $httpMethod = str::upper($request->method());
        $method = AnnotationsUtils::getOfClass($httpMethod, $clazz);

        if ($method == null) {
            $response->status(404, "Not found!");
            return;
        }

        try {
            $args = [];
            $method = $clazz->getMethod($method);
            $filter = fn($data) => $data;

            foreach ($method->getParameters() as $parameter) {
                if (($type = $parameter->getType()) != null) {
                    if ($type->getName() == HttpServerRequest::class) {
                        $args[] = $request;
                    } elseif ($type->getName() == HttpServerResponse::class) {
                        $args[] = $response;
                    } else {
                        $args[] = $this->__context->get($type->getName());
                    }
                } else {
                    try {
                        $args[] = $parameter->getDefaultValue();
                    } catch (ReflectionException $exception) {
                        $args[] = null;
                    }
                }
            }

            try {
                $data = null;

                try {
                    $data = $method->invokeArgs($this, $args);
                } catch (RedirectException $exception) {
                    (new HttpRedirectHandler($exception->getLocation()))($request, $response);
                } catch (Throwable $exception) {
                    $data = [
                        "path" => $request->path(),
                        "message" => $exception->getMessage(),
                        "code" => 500
                    ];

                    if ($exception instanceof WebException) {
                        $data["code"] = $exception->getHttpCode();
                    }

                    $response->status($data["code"]);
                }

                /** @var Filter $filter */
                foreach ($this->__context->lookup(fn($key, $value) => $value instanceof Filter) as $filter) {
                    if ($filter->filter($clazz, $request, $response, $data)) break;
                }
            } catch (Throwable $exception) {
                Logger::error("Fatal error in {0} on path '{1}': {2}", get_class($this), $request->path(), $exception->getMessage());
                $response->status(500, "Internal server error");
            }
        } catch (ReflectionException $exception) {
            $response->status(404, "Not found!");
        }
    }
}
