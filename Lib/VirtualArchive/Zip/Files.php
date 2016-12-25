<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class Files implements IVirtualComponent {

    // central directory end default length
    const CENTRAL_DIRECTORY_END_DEFAULT_LENGTH = 22;

    // central directory end maximum search limit
    const CENTRAL_DIRECTORY_END_MAXIMUM_LENGTH = 1000;

    // central directory end signature
    const CENTRAL_DIR_END_SIGNATURE = "\x50\x4b\x05\x06";

    // data file header signature
    const DATA_FILE_HEADER_SIGNATURE = "\x50\x4b\x03\x04";

    // central directory file signature
    const CENTRAL_DIR_FILE_HEADER_SIGNATURE = "\x50\x4b\x01\x02";

    // version made by
    const HEADER_VERSION_MADE_BY = "\x14\x00";

    // version needed to extract
    const HEADER_VERSION_REQUIRED = "\x14\x00";

    // general purpose bit flags
    const HEADER_GENERAL_FLAGS = "\x00\x00";

    // compression method
    const HEADER_COMPRESSION_METHOD = "\x08\x00";

    // last mod time
    const HEADER_LAST_MOD_TIME = "\x00\x00";

    // last mod date
    const HEADER_LAST_MOD_DATE = "\x00\x00";

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
     * Class constructor.
     * @param VirtualArchive $archive
     * @param array $params
     */
    public function __construct(VirtualArchive $archive, array $params) {

        // set archive
        $this->_archive = $archive;

        // set content
        $this->_content = $params['files'];
        foreach ($this->_content as $file) {
            $file->setArchive($this->_archive);
        }

        // reset
        $this->reset();

    }

    /**
     * Reset all data
     */
    public function reset() {

        // reset content
        reset($this->_content);

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

            // no more content
            if (!$this->hasMoreContent()) {
                break;
            }

            // finished reading $count bytes
            $bytesToRead = $count - strlen($bytes);
            if ($bytesToRead <= 0) {
                break;
            }

            // get current file
            $file = current($this->_content);

            if ($file->hasMoreContent()) {
                $bytes .= $file->read($bytesToRead);
            } else {
                next($this->_content);
            }

        }

        return $bytes;

    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {

        if ($this->_hasMoreContent) {
            $this->_hasMoreContent = current($this->_content) === false;
        }

        return $this->_hasMoreContent;

    }

}