<?php

namespace kawaii\core;

/**
 * Class Context
 * @package kawaii\core
 */
class Context
{
    /**
     * @var array
     */
    private array $beans = [];

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->set(Context::class, $this);
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

    /**
     * @param callable $callback ($key, $value): bool
     * @return array
     */
    public function lookup(callable $callback = null): array {
        $out = [];

        if ($callback == null)
            $callback = fn($key, $bean) => true;

        foreach ($this->beans as $key => $bean) {
            if ($callback($key, $bean))
                $out[] = $bean;
        }

        return $out;
    }
}
