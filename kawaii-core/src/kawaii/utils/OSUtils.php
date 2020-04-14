<?php


namespace kawaii\utils;

use php\lang\System;
use php\lib\str;

/**
 * Class OSUtils
 * @package kawaii\utils
 */
class OSUtils
{
    /**
     * @return bool
     */
    public static function isWindows(): bool {
        return str::posIgnoreCase(System::osName(), 'win') > -1;
    }

    /**
     * @return bool
     */
    public static function isUnix(): bool {
        return !static::isWindows();
    }
}