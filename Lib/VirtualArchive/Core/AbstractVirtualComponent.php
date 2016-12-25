<?php
namespace Lib\VirtualArchive\Core;
use Lib\VirtualArchive\Zip\Constants;
use Lib\VirtualArchive\Zip\VirtualArchive;

abstract class AbstractVirtualComponent {

    /**
     * @var VirtualArchive
     */
    protected $_archive;

    /**
     * @var
     */
    protected $_status = Constants::STATUS_NOT_STARTED;

    /**
     * Set archive
     *
     * @param VirtualArchive $archive
     */
    public function setArchive($archive) {
        $this->_archive = $archive;
    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {

        switch ($this->_status) {
            case Constants::STATUS_NOT_STARTED:
            case Constants::STATUS_PROCESSING:
                $hasMoreContent = true;
                break;
            default:
                $hasMoreContent = false;
                break;
        }

        return $hasMoreContent;

    }

    /**
     * Event fired when reading starts
     */
    public function onStartReading() {

        // update status
        $this->_status = Constants::STATUS_PROCESSING;

    }

    /**
     * Event fired when reading stops
     */
    public function onFinishReading() {

        // update status
        $this->_status = Constants::STATUS_DONE;

    }

    /**
     * Reset data
     */
    public function reset() {

        // reset status
        $this->_status = Constants::STATUS_NOT_STARTED;

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

        $bytes = '';
        if ($this->_status == Constants::STATUS_DONE) {
            return $bytes;
        }

        if ($this->_status == Constants::STATUS_NOT_STARTED) {
            $this->onStartReading();
        }

        // read data
        $bytes = $this->_read($count);

        if ($this->_status == Constants::STATUS_ALMOST_DONE) {
            $this->onFinishReading();
        }

        return $bytes;

    }

    /**
     * Read data
     *
     * @param int $count How many bytes of data from the current position should be returned
     * @return string If there are less than count bytes available, return as many as are available.
     *                If no more data is available, return an empty string.
     *
     */
    abstract protected function _read(int $count);

}