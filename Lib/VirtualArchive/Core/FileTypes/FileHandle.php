<?php
namespace Lib\VirtualArchive\Core\FileTypes;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Interfaces\IFile;

class FileHandle implements IFile {

    /**
     * @var resource File handle
     */
    protected $_handle;

    /**
     * Class constructor.
     *
     * @param resource $handle
     * @throws \Exception
     */
    public function __construct($handle) {

        // check parameter
        if (!is_resource($handle)) {
            throw new \Exception('Invalid file handle');
        }

        // set file handle
        $this->_handle = $handle;

    }

    /**
     * Move read pointer
     *
     * @param int $offset
     * @param int $whence
     * @return int Upon success, returns 0; otherwise, returns -1. Note that seeking
     */
    public function seek(int $offset, int $whence = SEEK_SET) {
        return fseek($this->_handle, $offset, $whence);
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

        $bytes = '';
        $bytesLeft = $count;

        while ($bytesLeft > 0) {

            $bytes .= fread($this->_handle, min($bytesLeft, Constants::READ_BUFFER));
            if (feof($this->_handle)) {
                break;
            }

            $bytesLeft = $count - strlen($bytes);

        }

        return $bytes;

    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int The position of the file pointer
     */
    public function tell() {
        return ftell($this->_handle);
    }

    /**
     * Return true if end of file
     *
     * @return bool
     */
    public function eof() {
        return feof($this->_handle);
    }

    /**
     * Truncates a file to a given length
     *
     * @param int $size The size to truncate to
     * @return bool true on success or false on failure.
     */
    public function truncate(int $size) {
        return ftruncate($this->_handle, $size);
    }

    /**
     * Write to file
     *
     * @param string $bytes
     */
    public function write(string $bytes) {
        fwrite($this->_handle, $bytes);
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getSize() {
        return fstat($this->_handle)['size'];
    }

}