<?php


namespace kawaii\core;

use kawaii\utils\Logger;
use php\lib\str;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

/**
 * Class Component
 * @package kawaii\core
 */
abstract class Component
{
    protected Context $__context;

    /**
     * Component constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->__context = $context;

        foreach ($this->getComponents() as $component)
            $this->__addComponent($component);

        try {
            $clazz = new ReflectionClass($this);
            $methods = $clazz->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);
            foreach ($methods as $method) {
                if (str::endsWith($method->getName(), "Bean")) {
                    $args = [];
                    foreach ($method->getParameters() as $parameter) {
                        if (($type = $parameter->getType()) != null) {
                            $args[] = $this->__context->get($type->getName());
                        } else {
                            try {
                                $args[] = $parameter->getDefaultValue();
                            } catch (ReflectionException $exception) {
                                $args[] = null;
                            }
                        }
                    }

                    $this->__addComponent($method->invokeArgs($this, $args));
                }
            }
        } catch (Throwable $exception) {
            Logger::warn("Error initializing {0} component due to exception: {1}",
                get_class($this),
                $exception->getMessage());
        }
    }

    /**
     * @return string[]
     */
    public function getComponents(): array {
        return [];
    }

    /**
     * @param $component
     * @return mixed
     */
    public function __addComponent($component) {
        if (is_string($component))
            $component = new $component($this->__context);

        //Logger::trace("Register component {0} in global context", get_class($component));
        return $this->__context->set(get_class($component), $component);
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
                    return $this->__context->get($type);
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
                    $this->__context->set($type, $value);
                }
            }
        }
    }
}
