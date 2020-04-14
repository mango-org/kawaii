<?php


namespace kawaii\utils;


use php\util\Regex;
use php\util\RegexException;
use ReflectionClass;
use ReflectionFunctionAbstract;

/**
 * Class AnnotationsUtils
 * @package kawaii\utils
 */
class AnnotationsUtils
{
    /**
     * @param string $annotationName
     * @param ReflectionClass $reflection
     * @param null $default
     * @return mixed
     * @throws RegexException
     */
    public static function getOfClass(string $annotationName, ReflectionClass $reflection, $default = null)
    {
        do {
            $var = static::get($annotationName, $reflection->getDocComment(), null);

            if (!$var) {
                $reflection = $reflection->getParentClass();
                if (!$reflection)
                    return $default;
            } else break;
        } while (true);

        return $var;
    }

    /**
     * @param string $annotationName
     * @param ReflectionFunctionAbstract $reflection
     * @param null $default
     * @return mixed
     * @throws RegexException
     */
    public static function getOfMethod(string $annotationName, ReflectionFunctionAbstract $reflection, $default = null)
    {
        return static::get($annotationName, $reflection->getDocComment(), $default);
    }

    /**
     * @param string $annotationName
     * @param string $comment
     * @param mixed $default
     * @return mixed
     * @throws RegexException
     */
    public static function get(string $annotationName, string $comment, $default = null)
    {
        if (!$comment) {
            return $default;
        }

        return static::parse($comment)[$annotationName] ?: $default;
    }

    /**
     * @param ReflectionFunctionAbstract $reflection
     * @return array
     * @throws RegexException
     */
    public static function parseMethod(ReflectionFunctionAbstract $reflection): array
    {
        return static::parse($reflection->getDocComment());
    }

    /**
     * @param ReflectionClass $reflection
     * @return array
     * @throws RegexException
     */
    public static function parseClass(ReflectionClass $reflection): array
    {
        return static::parse($reflection->getDocComment());
    }

    /**
     * @param string $comment
     * @param callable|null $callback
     * @return array
     * @throws RegexException
     */
    public static function parse(string $comment, callable $callback = null): array
    {
        $regex = new Regex('\\@([a-z0-9\\-\\_]+)([ ]+(.+))?', 'im', $comment);

        $result = [];

        while ($regex->find()) {
            $groups = $regex->groups();

            $name = $groups[1];
            $value = $groups[3] ?: true;

            if ($callback)
                if (!$callback($name, $value))
                    break;

            if ($result[$name]) {
                if (!is_array($result[$name])) {
                    $result[$name] = [$result[$name]];
                }
                $result[$name][] = $value;
            } else
                $result[$name] = $value;
        }

        return $result;
    }
}
