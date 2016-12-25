<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class Headers extends AbstractVirtualComponent implements IVirtualComponent {

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
        $this->_content = "";

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

        // update content position
        $this->_position += strlen($bytes);

        // update archive position
        $this->_archive->incrementPosition(strlen($bytes));

        // mark end of content
        if ($this->_position >= strlen($this->_content)) {
            $this->_status = Constants::STATUS_ALMOST_DONE;
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