<?php

namespace kawaii\web;

use kawaii\core\Application;
use kawaii\utils\AnnotationsUtils;
use kawaii\utils\Logger;
use kawaii\web\filter\JsonFilter;
use kawaii\web\filter\RawStringFilter;
use php\http\HttpServer;
use php\io\IOException;
use ReflectionClass;
use Throwable;

abstract class WebApplication extends Application
{
    /**
     * WebApplication constructor.
     * @throws IOException
     */
    public function __construct()
    {
        parent::__construct();

        $this->__addComponent(JsonFilter::class);
        $this->__addComponent(RawStringFilter::class);

        /** @var $httpServer HttpServer */
        $httpServer = $this->__context->get(HttpServer::class);
        foreach ($this->__context->lookup(fn ($key, $value) => $value instanceof Controller) as $controller) {
            $controller = $this->__addComponent($controller);

            try {
                $path = AnnotationsUtils::getOfClass("Path", new ReflectionClass($controller), "/");
                $httpServer->any($path, $controller);
                Logger::info("{0} successfully mapped to '{1}'", get_class($controller), $path);
            } catch (Throwable $exception) {
                Logger::error("Error adding {0} to HttpServer: {1}", get_class($controller), $exception->getMessage());
            }
        }
    }

    /**
     * @return HttpServer
     */
    protected function httpServerBean(): HttpServer {
        return new HttpServer(9657);
    }

    public function run() {
        /** @var $httpServer HttpServer */
        $httpServer = $this->__context->get(HttpServer::class);
        $httpServer->run();;
    }
}
