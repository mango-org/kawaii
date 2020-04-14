<?php


namespace kawaii\core;

use php\lib\str;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class Component
 * @package kawaii\core
 */
abstract class Component
{
    protected DI $__dependencyInjection;

    /**
     * Component constructor.
     * @param DI $dependencyInjection
     */
    public function __construct(DI $dependencyInjection)
    {
        $this->__dependencyInjection = $dependencyInjection;
    }

    /**
     * @param $component
     * @return mixed
     */
    public function addComponent($component) {
        if (is_string($component))
            $component = new $component($this->__dependencyInjection);

        return $this->__dependencyInjection->set(get_class($component), $component);
    }

    /**
     * @param $name
     * @return mixed
     * @throws ReflectionException
     */
    public function __get($name)
    {
        if (str::startsWith($name, "__"))
            return parent::__get($name);

        $clazz = new ReflectionClass($this);
        foreach ($clazz->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED)
                 as $property) {
            if ($name == $property->getName()) {
                $type = $property->getType()->getName();
                if ($type == null) {
                    return $property->getValue($this);
                } else {
                    return $this->__dependencyInjection->get($type);
                }
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     * @throws ReflectionException
     */
    public function __set($name, $value) {
        if (str::startsWith($name, "__"))
            parent::__set($name, $value);

        $clazz = new ReflectionClass($this);
        foreach ($clazz->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED)
                 as $property) {
            if ($name == $property->getName()) {
                $type = $property->getType()->getName();
                if ($type == null) {
                    $property->setValue($this, $value);
                } else {
                    $this->__dependencyInjection->set($type, $value);
                }
            }
        }
    }
}
