<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class VirtualFiles extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var IVirtualComponent[]
     */
    protected $_content;

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

    }

    /**
     * Reset all data
     */
    public function reset() {

        // call parent method
        parent::reset();

        // reset current file
        $this->_currentFile = null;

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

        $bytes = '';
        $bytesToRead = $count;

        while ($bytesToRead > 0) {

            // if no current file
            if ($this->_currentFile == null) {
                $this->_currentFile = $this->_getNextFile();
            }

            if ($this->_currentFile) {

                // read from current file
                $bytes .= $this->_currentFile->read($bytesToRead);

                // adjust bytes left to read
                $bytesToRead = $count - strlen($bytes);

                // if current file has no more content
                if (!$this->_currentFile->hasMoreContent()) {

                    // move to next file
                    $this->_currentFile = $this->_getNextFile();

                    // check if there is a next file
                    if ($this->_currentFile == null) {
                        break;
                    }

                }

            } else {
                break;
            }

        }

        return $bytes;

    }

    /**
     * Get next file
     */
    protected function _getNextFile() {

        if ($this->_currentFile) {

            // get next element in array
            $file = next($this->_content);

        } else {

            // get first element in array
            $file = reset($this->_content);

        }

        if ($file) {

            // set archive
            $file->setArchive($this->_archive);

            // reset file
            $file->reset();

        } else  {

            // mark end of content
            $this->_status = Constants::STATUS_ALMOST_DONE;

        }

        return $file;

    }

}