<?php
/**
 * @author  Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package App
 * @since   2015-10-14
 */

namespace Core;

/**
 * Common filters
 */
class Filter
{
    /**
     * Transforms a string into camel case
     *
     * @param  string $value String to be transformed
     *
     * @return string
     */
    public static function camelCase($value)
    {
        $value = trim($value);
        if (!empty($value)) {
            $value = \str_replace('_', '', \ucwords($value, '_'));
            $value[0] = \mb_strtolower($value[0]);
        }
        return $value;
    }
    
    /**
     * Transforms a string from camel to normal case
     *
     * @param  string $value     String to be transformed
     * @param  string $separator Separator (default: whitespace)
     *
     * @return string
     */
    public static function uncamelCase($value, $separator = ' ')
    {
        $value = trim($value);
        if (empty($value)) {
            return $value;
        }
        return \mb_strtolower(\preg_replace('/([a-z0-9])([A-Z])/', "$1{$separator}$2", $value));
    }
}
