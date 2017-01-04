<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;
use Lib\VirtualArchive\Zip\Maps\EndOfCentralDirectoryMap;

class VirtualEndOfCentralDirectory extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var string Content
     */
    protected $_content;

    /**
     * @var string Path to an empty archive
     */
    protected $_baseZipPath = '/../Resources/empty.zip';

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
            $this->_state = Constants::STATE_ALMOST_DONE;
        }

        return $bytes;

    }

    /**
     * Set attribute
     *
     * @param string $name Name of the attribute
     * @param mixed $value Value of the attribute
     */
    public function setAttribute(string $name, $value) {
        EndOfCentralDirectoryMap::setAttribute($this->_content, $name, $value);
    }

    /**
     * Get attribute
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name) {
        return EndOfCentralDirectoryMap::getAttribute($this->_content, $name);
    }

    /**
     * Get attribute
     *
     * @param string $name
     * @param int $value
     */
    public function incrementAttribute(string $name, int $value) {
        $this->setAttribute($name, $this->getAttribute($name) + $value);
    }

}