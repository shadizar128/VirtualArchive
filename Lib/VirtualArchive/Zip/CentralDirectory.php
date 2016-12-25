<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class CentralDirectory extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var string Path to an empty archive
     */
    protected $_baseZipPath = '/../Resources/empty.zip';

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
     * @var string Content
     */
    protected $_content;

    /**
     * @var int Cursor position
     */
    protected $_position = 0;

    /**
     * Reset data
     */
    public function reset() {

        // call parent method
        parent::reset();

        // reset position
        $this->_position = 0;

        // reset content
        $this->_content = file_get_contents(dirname(__FILE__) . $this->_baseZipPath);

    }

    /**
     * Read data
     *
     * @param int $count How many bytes of data from the current position should be returned
     * @return string If there are less than count bytes available, return as many as are available.
     *                If no more data is available, return an empty string.
     *
     */
    protected function _read(int $count) {

        // read data
        $bytes = substr($this->_content, $this->_position, $count);

        // get number of bytes read
        $bytesRead = strlen($bytes);

        // update content position
        $this->_position += $bytesRead;

        // update archive position
        $this->_archive->incrementPosition(strlen($bytes));

        // mark end of content
        if ($this->_position >= strlen($this->_content)) {
            $this->_status = Constants::STATUS_ALMOST_DONE;
        }

        return $bytes;

    }

    /**
     * Set attribute
     *
     * @param string $name Name of the attribute
     * @param int $value Value of the attribute
     */
    public function setAttribute($name, $value) {
        $properties = $this->_map[$name];
        $this->_content = substr_replace($this->_content, pack($properties['method'], $value), $properties['offset'], $properties['length']);
    }

    /**
     * Get attribute
     *
     * @param string $name Name of the attribute
     * @return mixed
     */
    public function getAttribute($name) {
        $properties = $this->_map[$name];
        $value = substr($this->_content, $properties['offset'], $properties['length']);
        $value = unpack($properties['method'], $value);
        return array_pop($value);
    }

    /**
     * Get attribute
     *
     * @param string $name Name of the attribute
     * @param $value
     */
    public function incrementAttribute($name, $value) {
        $this->setAttribute($name, $this->getAttribute($name) + $value);
    }

}