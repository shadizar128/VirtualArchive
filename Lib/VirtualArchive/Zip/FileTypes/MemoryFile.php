<?php
namespace Lib\VirtualArchive\Zip\FileTypes;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;
use Lib\VirtualArchive\Zip\Constants;
use Lib\VirtualArchive\Zip\VirtualArchive;

class MemoryFile implements IVirtualComponent {

    /**
     * @var VirtualArchive
     */
    protected $_archive;

    /**
     * @var string
     */
    protected $_content;

    /**
     * @var bool True if there is more content to read
     */
    protected $_hasMoreContent = true;

    /**
     * @var int Cursor position
     */
    protected $_position = 0;

    /**
     * @var bool Should add metadata
     */
    protected $_addMetadata;

    /**
     * @var array File metadata
     */
    protected $_metadata;

    /**
     * Class constructor.
     *
     * @param string $fileName
     * @param string $content
     */
    public function __construct(string $fileName, string $content) {

        $this->_metadata = [];
        $this->_metadata['fileName'] = $fileName;
        $this->_metadata['crc'] = crc32($content);
        $this->_metadata['uncompressedSize'] = strlen($content);

        // compress file and get new size
        $bytes = substr(gzcompress($content), 2, -4);

        $this->_metadata['compressedSize'] = strlen($bytes);

        // set content
        $this->_content  = Constants::FILE_SIGNATURE;
        $this->_content .= Constants::VERSION_REQUIRED;
        $this->_content .= Constants::GENERAL_FLAGS;
        $this->_content .= Constants::COMPRESSION_METHOD;
        $this->_content .= Constants::LAST_MODIFIED_TIME;
        $this->_content .= Constants::LAST_MODIFIED_DATE;
        $this->_content .= pack("V", $this->_metadata['crc']);
        $this->_content .= pack("V", $this->_metadata['compressedSize']);
        $this->_content .= pack("V", $this->_metadata['uncompressedSize']);
        $this->_content .= pack("v", strlen($this->_metadata['fileName']));
        $this->_content .= pack("v", 0);
        $this->_content .= $this->_metadata['fileName'];
        $this->_content .= $bytes;
        $this->_content .= pack("V", $this->_metadata['crc']);
        $this->_content .= pack("V", $this->_metadata['compressedSize']);
        $this->_content .= pack("V", $this->_metadata['uncompressedSize']);

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
    }

    /**
     * Reset all data
     */
    public function reset() {

        // reset counters
        $this->_position = 0;
        $this->_hasMoreContent = true;
        $this->_addMetadata = true;

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

        if ($this->_addMetadata) {
            $this->addMetadata();
        }

        $bytes = "";
        if (!$this->hasMoreContent()) {
            return $bytes;
        }

        // read data
        $bytes = substr($this->_content, $this->_position, $count);

        // get number of bytes read
        $bytesRead = strlen($bytes);

        // update content position
        $this->_position += $bytesRead;

        // update archive position
        $this->_archive->incrementPosition($bytesRead);

        return $bytes;

    }

    /**
     * Add metadata to archive headers
     */
    public function addMetadata() {

        $this->_addMetadata = false;

        $header  = Constants::HEADER_SIGNATURE;
        $header .= Constants::VERSION_MADE_BY;
        $header .= Constants::VERSION_REQUIRED;
        $header .= Constants::GENERAL_FLAGS;
        $header .= Constants::COMPRESSION_METHOD;
        $header .= Constants::LAST_MODIFIED_TIME;
        $header .= Constants::LAST_MODIFIED_DATE;
        $header .= pack("V", $this->_metadata['crc']);
        $header .= pack("V", $this->_metadata['compressedSize']);
        $header .= pack("V", $this->_metadata['uncompressedSize']);
        $header .= pack("v", strlen($this->_metadata['fileName']));
        $header .= pack("v", 0);
        $header .= pack("v", 0);
        $header .= pack("v", 0);
        $header .= pack("v", 0);
        $header .= pack("V", 32);
        $header .= pack("V", $this->_archive->getPosition());
        $header .= $this->_metadata['fileName'];

        $this->_archive->getHeaders()->add($header);

        // update end of central directory
        $centralDirectory = $this->_archive->getCentralDirectory();
        $centralDirectory->incrementAttribute('centralDirectorySize', strlen($header));
        $centralDirectory->incrementAttribute('centralDirectoryOffset', strlen($this->_content));
        $centralDirectory->incrementAttribute('totalEntries', 1);
        $centralDirectory->incrementAttribute('diskEntries', 1);

    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {

        if ($this->_hasMoreContent) {
            $this->_hasMoreContent = $this->_position < strlen($this->_content);
        }

        return $this->_hasMoreContent;

    }

}