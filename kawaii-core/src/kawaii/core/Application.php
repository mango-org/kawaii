<?php

namespace kawaii\core;

use kawaii\utils\Logger;
use kawaii\utils\OSUtils;
use php\io\IOException;
use system\DFFIConsole;

/**
 * Class Application
 * @package kawaii\core
 */
abstract class Application extends Component
{
    /**
     * Application constructor.
     * @throws IOException
     */
    public function __construct()
    {
        // init logger
        if (OSUtils::isWindows()) {
            DFFIConsole::enableColors();
        }

        Logger::addWriter(Logger::stdoutWriter(OSUtils::isUnix() || DFFIConsole::hasColorSupport()));

        parent::__construct(new Context()); // create new DI instance ...
    }

    /**
     * @param string $clazz
     * @return mixed
     */
    public function getComponent(string $clazz) {
        return $this->__context->get($clazz);
    }
}
