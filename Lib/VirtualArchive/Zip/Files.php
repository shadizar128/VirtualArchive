<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class Files implements IVirtualComponent {

    /**
     * @var VirtualArchive
     */
    protected $_archive;

    /**
     * @var IVirtualComponent[]
     */
    protected $_content;

    /**
     * @var bool True if there is more content to read
     */
    protected $_hasMoreContent = true;

    /**
     * @var IVirtualComponent
     */
    protected $_currentFile;

    /**
     * Class constructor.
     *
     * @param array $params
     */
    public function __construct(array $params) {

        // set content
        $this->_content = $params['files'];

        // reset
        $this->reset();

    }

    /**
     * Set archive
     *
     * @param VirtualArchive $archive
     */
    public function setArchive($archive) {

        $this->_archive = $archive;
        foreach ($this->_content as $file) {
            $file->setArchive($this->_archive);
        }

    }

    /**
     * Reset all data
     */
    public function reset() {

        // reset all files
        foreach ($this->_content as $file) {
            $file->reset();
        }

        // reset current file
        $this->_currentFile = null;

        // reset counters
        $this->_hasMoreContent = true;

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

        $bytes = "";
        while (true) {

            if ($this->_hasMoreContent == false) {
                return $bytes;
            }

            // finished reading $count bytes
            $bytesToRead = $count - strlen($bytes);
            if ($bytesToRead <= 0) {
                break;
            }

            if ($this->_currentFile == null) {
                $this->_moveToNextFile();
            }

            if ($this->_currentFile) {
                $bytes .= $this->_currentFile->read($bytesToRead);
            }

            if (!$this->_currentFile->hasMoreContent()) {
                $this->_moveToNextFile();
            }

        }

        return $bytes;

    }

    /**
     * Move cursor to the next file
     */
    protected function _moveToNextFile() {

        if ($this->_currentFile) {

            // handle stop reading event for previous file
            $this->_currentFile->onFinishReading();

            // move to next element in array
            $this->_currentFile = next($this->_content);

        } else {

            // get first element in array
            $this->_currentFile = reset($this->_content);

        }

        if ($this->_currentFile) {

            // handle start reading event for new file
            $this->_currentFile->onStartReading();

        } else {

            // mark end of content
            $this->_hasMoreContent = false;

        }

    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {
        return $this->_hasMoreContent;
    }

    /**
     * Event fired when reading starts
     */
    public function onStartReading() {
        $this->reset();
    }

    /**
     * Event fired when reading stops
     */
    public function onFinishReading() {
        // nothing to do here
    }

}