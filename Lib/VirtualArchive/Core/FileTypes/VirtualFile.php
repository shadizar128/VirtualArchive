<?php
namespace Lib\VirtualArchive\Core\FileTypes;
use Lib\VirtualArchive\Interfaces\IFile;

class VirtualFile implements IFile {

    /**
     * @var IFile
     */
    protected $_base;

    /**
     * @var int
     */
    protected $_start;

    /**
     * @var int
     */
    protected $_stop;

    /**
     * @var int
     */
    protected $_pointer;

    /**
     * Class constructor.
     *
     * @param IFile $base
     * @param int $start
     * @param int $stop
     * @throws \Exception
     */
    public function __construct(IFile $base, int $start, int $stop) {

        if ($start < 0) {
            $start = 0;
        }

        $fileSize = $base->getSize();

        if ($start > $fileSize) {
            throw new \Exception('Invalid start position');
        }

        if ($stop <= $start) {
            throw new \Exception('Invalid stop position');
        }

        if ($stop > $fileSize) {
            $stop = $fileSize;
        }

        $this->_base = $base;
        $this->_start = $start;
        $this->_stop = $stop;
        $this->_pointer = $this->_start;

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
                $this->_pointer = $this->_start + $offset;
                $result = true;
                break;
            case SEEK_CUR:
                $this->_pointer += $offset;
                $result = true;
                break;
            case SEEK_END:
                $this->_pointer = $this->_stop + $offset;
                $result = true;
                break;
            default:
                $result = false;
                break;
        }

        $this->_pointer = max($this->_pointer, $this->_start);
        $this->_pointer = min($this->_pointer, $this->_stop);

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

        // save base file pointer
        $baseFilePointer = $this->_base->tell();

        // move base file pointer to virtual file pointer
        $this->_base->seek($this->_pointer);

        // resize count based on virtual file size and pointer
        $count = min($count, $this->_stop - $this->_pointer);

        // read bytes from base file
        $bytes = $this->_base->read($count);

        // update virtual file pointer
        $this->_pointer += strlen($bytes);

        // restore base file pointer
        $this->_base->seek($baseFilePointer);

        // return bytes
        return $bytes;

    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int The position of the file pointer
     */
    public function tell() {
        return $this->_pointer - $this->_start;
    }

    /**
     * Return true if end of file
     *
     * @return bool
     */
    public function eof() {
        return $this->_pointer >= $this->_stop;
    }

    /**
     * Truncates a file to a given length
     *
     * @param int $size The size to truncate to
     * @return bool true on success or false on failure.
     * @throws \Exception
     */
    public function truncate(int $size) {
        throw new \Exception('Operation not supported');
    }

    /**
     * Write to file
     *
     * @param string $bytes
     * @throws \Exception
     */
    public function write(string $bytes) {
        throw new \Exception('Operation not supported');
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getSize() {
        return $this->_stop - $this->_start;
    }

}