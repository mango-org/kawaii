<?php

namespace kawaii\core;

/**
 * Class DI
 * @package kawaii\core
 */
class DI
{
    /**
     * @var array
     */
    private array $beans = [];

    /**
     * DI constructor.
     */
    public function __construct()
    {
        $this->set(DI::class, $this);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key) {
        return $this->beans[$key];
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function set(string $key, $value) {
        return $this->beans[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return isset($this->beans[$key]);
    }
}
