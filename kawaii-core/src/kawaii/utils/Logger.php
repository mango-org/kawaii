<?php

namespace kawaii\utils;

use php\io\IOException;
use php\io\Stream;
use php\lib\char;
use php\lib\str;
use php\time\Time;

/**
 * Class Logger
 * @package kawaii\utils
 */
class Logger
{
    protected static array $ANSI_CODES = [
        "off" => 0,
        "bold" => 1,
        "italic" => 3,
        "underline" => 4,
        "blink" => 5,
        "inverse" => 7,
        "hidden" => 8,
        "gray" => 30,
        "red" => 31,
        "green" => 32,
        "yellow" => 33,
        "blue" => 34,
        "magenta" => 35,
        "cyan" => 36,
        "silver" => "0;37",
        "white" => 37,
        "black_bg" => 40,
        "red_bg" => 41,
        "green_bg" => 42,
        "yellow_bg" => 43,
        "blue_bg" => 44,
        "magenta_bg" => 45,
        "cyan_bg" => 46,
        "white_bg" => 47,
    ];

    protected static array $LEVEL_COLORS = [
        'DEBUG' => 'silver',
        'WARN'  => 'bold+yellow',
        'ERROR' => 'bold+red',
        'TRACE' => 'cyan',
        'INFO'  => 'bold+blue'
    ];

    protected static array $writeHandlers = [];
    protected static array $scopeLevels = [
        'root' => 'DEBUG'
    ];

    protected static array $levels = [
        'ERROR' => 100,
        'WARN' => 200,
        'INFO' => 300,
        'DEBUG' => 400,
        'TRACE' => 500,
    ];

    protected static string $format = "{level} [{context}] ({time}) {message}";

    /**
     * @param string $format
     */
    public static function setFormat(string $format)
    {
        static::$format = $format;
    }

    /**
     * @return string
     */
    public static function getFormat(): string
    {
        return static::$format;
    }

    /**
     * @param callable $writeHandler
     * @param string $id
     */
    public static function addWriter(callable $writeHandler, string $id = 'general')
    {
        static::$writeHandlers[$id] = $writeHandler;
    }

    /**
     * @param string $level
     */
    public static function setLevel(string $level)
    {
        static::$scopeLevels['root'] = $level;
    }

    /**
     * @return string
     */
    public static function getLevel()
    {
        return static::$scopeLevels['root'] ?: 'DEBUG';
    }

    /**
     * @param bool $withColor
     * @return callable
     * @throws IOException
     */
    public static function stdoutWriter(bool $withColor = false): callable
    {
        $stdout = Stream::of("php://stdout");


        $format = function ($str, $color) {
            $color_attrs = explode("+", $color);
            $ansi_str = "";

            foreach ($color_attrs as $attr) {
                $ansi_str .= char::of(27) . "[" . static::$ANSI_CODES[$attr] . "m";
            }

            $ansi_str .= $str . char::of(27) . "[" . static::$ANSI_CODES["off"] . "m";
            return $ansi_str;
        };

        return function ($type, $message) use ($stdout, $format, $withColor) {
            if ($withColor) {
                $color = static::$LEVEL_COLORS[$type];

                if ($color) {
                    $message = $format($message, $color);
                }
            }

            $stdout->write("$message\n");
        };
    }

    /**
     * @param string $type
     * @param string $message
     * @param array ...$args
     * @return string
     */
    public static function format(string $type, string $message, ...$args): string
    {
        $message = str::replace($message, "{level}", $type);

        foreach ($args as $i => $arg) {
            $message = str::replace($message, "\{$i\}", $arg);
        }

        $result = str::replace(static::$format, '{message}', $message);
        $result = str::replace($result, '{level}', $type);
        $result = str::replace($result, '{time}', Time::now()->toString('HH:mm:ss'));

        return $result;
    }

    /**
     * @param string $type
     * @param $message
     * @param array ...$args
     */
    public static function log(string $type, string $message, ...$args)
    {
        $typeLevel = (int)static::$levels[$type];

        if ($typeLevel >= static::getLevel()) {
            $message = static::format($type, $message, ...$args);

            if (str::contains($message, '{context}')) {
                $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
                $context = 'Unknown';
                foreach ($stackTrace as $trace) {
                    if ($trace['class'] !== __CLASS__) {
                        $context = $trace['class'];
                        break;
                    }
                }

                $context = str::split($context, '\\');

                foreach ($context as $i => &$one) {
                    if ($i !== sizeof($context) - 1) {
                        $one = $one[0];
                    }
                }

                $context = str::join($context, '.');
                $message = str::replace($message, "{context}", $context);
            }

            foreach (static::$writeHandlers as $handler) {
                $handler($type, $message);
            }
        }
    }

    /**
     * @param string $message
     * @param array ...$args
     */
    public static function info(string $message, ...$args)
    {
        static::log('INFO', $message, ...$args);
    }

    /**
     * @param string $message
     * @param array ...$args
     */
    public static function debug(string $message, ...$args)
    {
        static::log('DEBUG', $message, ...$args);
    }

    /**
     * @param string $message
     * @param array ...$args
     */
    public static function trace(string $message, ...$args)
    {
        static::log('TRACE', $message, ...$args);
    }

    /**
     * @param string $message
     * @param array ...$args
     */
    public static function warn(string $message, ...$args)
    {
        static::log('WARN', $message, ...$args);
    }

    /**
     * @param string $message
     * @param array ...$args
     */
    public static function error(string $message, ...$args)
    {
        static::log('ERROR', $message, ...$args);
    }
}
