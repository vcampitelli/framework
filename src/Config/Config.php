<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Core\Config;

/**
 * Handles the configuration
 */
class Config extends Object
{
    /**
     * Reads a INI file
     *
     * @param  string $file File path
     *
     * @return self
     */
    public function readIni($file)
    {
        $arr = \parse_ini_file($file, true);
        $this->_data = $this->_parse($arr);
        return $this;
    }
    
    /**
     * Parses a section
     *
     * @param  array  $data Data to be parsed
     *
     * @return array        Parsed data
     */
    protected function _parse(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursive
                $data[$key] = $this->_parse($value);
            } elseif ((\strpos($value, '::') !== false) && (\defined($value))) {
                // Replaces Class::CONSTANT with the actual value
                $data[$key] = \constant($value);
            }
        }
        return $data;
    }
}
