<?php
namespace Lib\VirtualArchive\Interfaces;

interface IFile {

    /**
     * Move read pointer
     *
     * @param int $offset
     * @param int $whence
     * @return int Upon success, returns 0; otherwise, returns -1. Note that seeking
     */
    public function seek(int $offset, int $whence = SEEK_SET);

    /**
     * Read data
     *
     * @param int $count How many bytes of data from the current position should be returned
     * @return string If there are less than count bytes available, return as many as are available.
     *                If no more data is available, return an empty string.
     *
     */
    public function read(int $count);

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int The position of the file pointer
     */
    public function tell();

    /**
     * Return true if end of file
     *
     * @return bool
     */
    public function eof();

    /**
     * Truncates a file to a given length
     *
     * @param int $size The size to truncate to
     * @return bool true on success or false on failure.
     */
    public function truncate(int $size);

    /**
     * Write to file
     *
     * @param string $bytes
     */
    public function write(string $bytes);

    /**
     * Get file size
     *
     * @return int
     */
    public function getSize();

}