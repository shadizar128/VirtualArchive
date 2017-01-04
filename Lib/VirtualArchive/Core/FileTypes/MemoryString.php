<?php
namespace Lib\VirtualArchive\Core\FileTypes;
use Lib\VirtualArchive\Interfaces\IFile;

class MemoryString implements IFile {

    /**
     * @var string
     */
    protected $_content;

    /**
     * @var int Read pointer
     */
    protected $_pointer = 0;

    /**
     * Class constructor.
     *
     * @param string $bytes
     */
    public function __construct(string $bytes) {

        $this->_content = $bytes;

    }

    /**
     * Move read pointer
     *
     * @param int $offset
     * @param int $whence
     * @return int Upon success, returns 0; otherwise, returns -1. Note that seeking
     */
    public function seek(int $offset, int $whence = SEEK_SET) {

        switch($whence) {
            case SEEK_SET:
                $this->_pointer = $offset;
                $result = true;
                break;
            case SEEK_CUR:
                $this->_pointer += $offset;
                $result = true;
                break;
            case SEEK_END:
                $this->_pointer = $this->getSize() + $offset;
                $result = true;
                break;
            default:
                $result = false;
                break;
        }

        return $result;

    }

    /**
     * Read data
     *
     * @param int $count How many bytes of data from the current position should be returned
     * @return string If there are less than count bytes available, return as many as are available.
     *                If no more data is available, return an empty string.
     *
     */
    public function read(int $count) {

        // nothing to do here
        if ($count <= 0) {
            return '';
        }

        // read data
        $bytes = substr($this->_content, $this->_pointer, $count);

        // get number of bytes read
        $bytesRead = strlen($bytes);

        // update content position
        $this->_pointer += $bytesRead;

        return $bytes;

    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int The position of the file pointer
     */
    public function tell() {
        return $this->_pointer;
    }

    /**
     * Return true if end of file
     *
     * @return bool
     */
    public function eof() {
        return $this->_pointer >= strlen($this->_content);
    }

    /**
     * Truncates a file to a given length
     *
     * @param int $size The size to truncate to
     * @return bool true on success or false on failure.
     */
    public function truncate(int $size) {
        $this->_content = substr($this->_content, 0, $size);
        $this->_pointer = 0;
        return true;
    }

    /**
     * Write to file
     *
     * @param string $bytes
     */
    public function write(string $bytes) {
        $this->_content .= $bytes;
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getSize() {
        return strlen($this->_content);
    }

}