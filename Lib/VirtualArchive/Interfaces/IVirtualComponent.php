<?php
namespace Lib\VirtualArchive\Interfaces;

interface IVirtualComponent {

    /**
     * Set archive
     * @param IVirtualArchive $archive
     */
    public function setArchive($archive);

    /**
     * Reset data
     */
    public function reset();

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
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent();

    /**
     * Event fired when reading starts
     */
    public function onStartReading();

    /**
     * Event fired when reading stops
     */
    public function onFinishReading();

}