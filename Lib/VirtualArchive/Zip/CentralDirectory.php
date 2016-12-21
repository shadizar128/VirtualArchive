<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class CentralDirectory implements IVirtualComponent {

    /**
     * @var string Path to an empty archive
     */
    protected $_baseZipPath = '';

    /**
     * @var array A map for the end of central directory
     */
    protected $_map = array(
        'diskEntries' => array(
            'offset' => 8,
            'length' => 2,
            'method'  => 'v'
        ),
        'totalEntries' => array(
            'offset' => 10,
            'length' => 2,
            'method'  => 'v'
        ),
        'centralDirectorySize' => array(
            'offset' => 12,
            'length' => 4,
            'method'  => 'V'
        ),
        'centralDirectoryOffset' => array(
            'offset' => 16,
            'length' => 4,
            'method'  => 'V'
        ),

    );

    /**
     * @var string Archive end structure
     */
    protected $_data;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->reset();
    }

    /**
     * Reset all data
     */
    public function reset() {
        $this->_data = file_get_contents($this->_baseZipPath);
    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {
        return false;
    }

    /**
     * Set attribute
     *
     * @param string $name Name of the attribute
     * @param int $value Value of the attribute
     */
    protected function _setAttribute($name, $value) {
        $properties = $this->_map[$name];
        $this->_data = substr_replace($this->_data, pack($properties['method'], $value), $properties['offset'], $properties['length']);
    }

    /**
     * Get attribute
     *
     * @param string $name Name of the attribute
     * @return mixed
     */
    protected function _getAttribute($name) {
        $properties = $this->_map[$name];
        $value = substr($this->_data, $properties['offset'], $properties['length']);
        $value = unpack($properties['method'], $value);
        return array_pop($value);
    }

}