<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Core\FileTypes\MemoryFile;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class VirtualCentralDirectoryHeaders extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var MemoryFile Content
     */
    protected $_content;

    /**
     * @var int Cursor position
     */
    protected $_position = 0;

    /**
     * Class constructor.
     */
    public function __construct() {

        // create empty content
        $this->_content = new MemoryFile('', '');

    }

    /**
     * Reset data
     */
    public function reset() {

        // call parent method
        parent::reset();

        // reset content
        $this->_content->truncate();

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
        $bytes = $this->_content->read($count);

        // update archive position
        $this->_archive->incrementPosition(strlen($bytes));

        // mark end of content
        if ($this->_content->eof()) {
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
        $this->_content->append($header);
    }

}