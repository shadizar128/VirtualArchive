<?php
namespace Lib\VirtualArchive\Interfaces;

interface IVirtualArchive {

    /**
     * Reset internal read pointers
     */
    public function reset();

    /**
     * Read $count bytes from the archive, null given when eof
     *
     * @param int $count Number of bytes to read
     * @return null|string
     *
     */
    public function read(int $count);

    /**
     * Get cursor position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Return true if end of archive
     *
     * @return bool
     */
    public function hasMoreContent();

}