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
     * @var VirtualArchive
     */
    protected $_archive;

    /**
     * @var string Content
     */
    protected $_content;

    /**
     * @var bool True if there is more content to read
     */
    protected $_hasMoreContent = true;

    /**
     * @var int Cursor position
     */
    protected $_position = 0;

    /**
     * Class constructor.
     * @param VirtualArchive $archive
     * @param array $params
     */
    public function __construct(VirtualArchive $archive, array $params) {

        // set archive
        $this->_archive = $archive;

        // reset
        $this->reset();

    }

    /**
     * Reset data
     */
    public function reset() {

        // reset content
        $this->_content = file_get_contents($this->_baseZipPath);

        // reset counters
        $this->_position = 0;
        $this->_hasMoreContent = true;

    }

    /**
     * Read data
     *
     * @param int $count How many bytes of data from the current position should be returned
     * @return string If there are less than count bytes available, return as many as are available.
     *                If no more data is available, return an empty string.
     *
     */
    public function read($count) {

        $bytes = "";
        if (!$this->hasMoreContent()) {
            return $bytes;
        }

        // read data
        $bytes = substr($this->_content, $this->_position, $count);

        // update position
        $this->_position += strlen($bytes);

        return $bytes;

    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {

        if ($this->_hasMoreContent) {
            $this->_hasMoreContent = $this->_position < strlen($this->_content);
        }

        return $this->_hasMoreContent;

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

}