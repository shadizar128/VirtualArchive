<?php
namespace Lib\VirtualArchive\Zip\Maps;

class AbstractZipMap {

    /**
     * @var array
     */
    protected static $_map;

    /**
     * Set attribute
     *
     * @param string $bytes
     * @param string $name Name of the attribute
     * @param mixed $value Value of the attribute
     */
    public static function setAttribute(string &$bytes, string $name, $value) {
        $properties = static::$_map[$name];
        if ($properties['method'] !== null) {
            $value = pack($properties['method'], $value);
        }
        $bytes = substr_replace($bytes, $value, $properties['offset'], $properties['length']);
    }

    /**
     * Get attribute
     *
     * @param string $bytes
     * @param string $name
     * @return mixed
     */
    public static function getAttribute(string &$bytes, string $name) {
        $properties = static::$_map[$name];
        $value = substr($bytes, $properties['offset'], $properties['length']);
        if ($properties['method'] !== null) {
            $value = unpack($properties['method'], $value);
            $value = array_pop($value);
        }
        return $value;
    }

    /**
     * Get attribute
     *
     * @param string $bytes
     * @param string $name
     * @param int $value
     */
    public function incrementAttribute(string &$bytes, string $name, int $value) {
        $this->setAttribute($bytes, $name, $this->getAttribute($bytes, $name) + $value);
    }

}