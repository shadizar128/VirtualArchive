<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Exceptions\FileNotFoundException;
use Lib\VirtualArchive\Interfaces\IVirtualArchive;

/**
 * Class that creates a virtual zip archive from other files without creating a new file on disk or storing the data in memory.
 * Inspired by this code: http://www.granthinkson.com/2005/07/01/create-zip-files-dynamically-using-php/
 * Zip file structure: https://users.cs.jmu.edu/buchhofp/forensics/formats/pkzip.html
 */
class VirtualArchive implements IVirtualArchive {

    /**
     * @var CentralDirectory
     */
    protected $_centralDirectory;

    /**
     * @var Headers
     */
    protected $_headers;

    /**
     * @var Files
     */
    protected $_files;

    /**
     * @var int Bytes read since the last reset
     */
    protected $_position;

    /**
     * @var bool Read status
     */
    protected $_status = Constants::STATUS_NOT_STARTED;

    /**
     * Public constructor
     *
     * @param array $params A list of parameters
     * @throws FileNotFoundException
     */
    public function __construct(array $params) {

        $this->_centralDirectory = new CentralDirectory($params);
        $this->_headers = new Headers($params);
        $this->_files = new Files($params);

        $this->reset();

    }

    /**
     * Cleanup method
     *
     */
    public function __destruct() {
        // TODO
    }

    /**
     * Reset internal read pointers
     */
    public function reset() {

        // reset central directory
        $this->_centralDirectory->setArchive($this);
        $this->_centralDirectory->reset();

        // reset headers
        $this->_headers->setArchive($this);
        $this->_headers->reset();

        // reset files
        $this->_files->reset();
        $this->_files->setArchive($this);

        // reset status
        $this->_status = Constants::STATUS_NOT_STARTED;

        // reset position
        $this->_position = 0;

    }

    /**
     * Read $count bytes from the object
     *
     * @param int $count Number of bytes to read
     * @return string
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
     * Read $count bytes from the object
     *
     * @param int $count Number of bytes to read
     * @return string
     *
     */
    protected function _read($count) {

        $bytes = "";
        $bytesToRead = $count - strlen($bytes);

        // read from files
        if ($this->_files->hasMoreContent() && $bytesToRead > 0) {
            $bytes .= $this->_files->read($bytesToRead);
            $bytesToRead = $count - strlen($bytes);
        }

        // read from headers
        if ($this->_headers->hasMoreContent() && $bytesToRead > 0) {
            $bytes .= $this->_headers->read($bytesToRead);
            $bytesToRead = $count - strlen($bytes);
        }

        // read from central directory
        if ($this->_centralDirectory->hasMoreContent() && $bytesToRead > 0) {
            $bytes .= $this->_centralDirectory->read($bytesToRead);
            $bytesToRead = $count - strlen($bytes);
        }

        if (
            !$this->_files->hasMoreContent() &&
            !$this->_headers->hasMoreContent() &&
            !$this->_centralDirectory->hasMoreContent()
        ) {
            $this->_status = Constants::STATUS_DONE;
        }

        return $bytes;

    }

    /**
     * Get cursor position
     *
     * @return int
     */
    public function getPosition() {
        return $this->_position;
    }

    /**
     * Update cursor position
     *
     * @param int $count
     */
    public function incrementPosition(int $count) {
        $this->_position += $count;
    }

    /**
     * Return true if the archive has more content
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
     * @return Headers
     */
    public function getHeaders() {
        return $this->_headers;
    }

    /**
     * @return CentralDirectory
     */
    public function getCentralDirectory() {
        return $this->_centralDirectory;
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

}