<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class Headers implements IVirtualComponent {

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
     *
     * @param array $params
     */
    public function __construct(array $params) {

        // reset
        $this->reset();

    }

    /**
     * Set archive
     *
     * @param VirtualArchive $archive
     */
    public function setArchive($archive) {
        $this->_archive = $archive;
    }

    /**
     * Reset data
     */
    public function reset() {

        // reset content
        $this->_content = "";

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
        if ($this->_hasMoreContent == false) {
            return $bytes;
        }

        // read data
        $bytes = substr($this->_content, $this->_position, $count);

        // update content position
        $this->_position += strlen($bytes);

        // update archive position
        $this->_archive->incrementPosition(strlen($bytes));

        // mark end of content
        if ($this->_position >= strlen($this->_content)) {
            $this->_hasMoreContent = false;
        }

        return $bytes;

    }



    /**
     * Add header
     *
     * @param string $header
     */
    public function add(string $header) {
        $this->_content .= $header;
    }

}