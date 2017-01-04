<?php
namespace Lib\VirtualArchive\Zip\FileTypes;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Core\FileTypes\MemoryString;
use Lib\VirtualArchive\Interfaces\IFile;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;
use Lib\VirtualArchive\Zip\ZipConstants;

class CompressedFile extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var array File metadata
     */
    protected $_metadata;

    /**
     * @var MemoryString
     */
    protected $_file;

    /**
     * Class constructor.
     *
     * @param IFile $file
     */
    public function __construct(IFile $file) {

        // set file
        $this->_file = $file;

        // compute metadata
        $this->_metadata = [];
        $this->_metadata['fileName'] = $this->_file->getFileName();
        $this->_metadata['uncompressedSize'] = $this->_file->getSize();

        // read all content of file
        $bytes = $this->_file->read($this->_metadata['uncompressedSize']);

        // compute crc
        $this->_metadata['crc'] = crc32($bytes);

        // compress content
        $bytes = substr(gzcompress($bytes), 2, -4);

        // get new size
        $this->_metadata['compressedSize'] = strlen($bytes);

        // truncate file content
        $this->_file->truncate(0);

        // add new content
        $this->_file->append(ZipConstants::LOCAL_FILE_SIGNATURE);
        $this->_file->append(ZipConstants::VERSION_REQUIRED);
        $this->_file->append(ZipConstants::GENERAL_FLAGS);
        $this->_file->append(ZipConstants::COMPRESSION_METHOD);
        $this->_file->append(ZipConstants::LAST_MODIFIED_TIME);
        $this->_file->append(ZipConstants::LAST_MODIFIED_DATE);
        $this->_file->append(pack("V", $this->_metadata['crc']));
        $this->_file->append(pack("V", $this->_metadata['compressedSize']));
        $this->_file->append(pack("V", $this->_metadata['uncompressedSize']));
        $this->_file->append(pack("v", strlen($this->_metadata['fileName'])));
        $this->_file->append(pack("v", 0));
        $this->_file->append($this->_metadata['fileName']);
        $this->_file->append($bytes);
        $this->_file->append(pack("V", $this->_metadata['crc']));
        $this->_file->append(pack("V", $this->_metadata['compressedSize']));
        $this->_file->append(pack("V", $this->_metadata['uncompressedSize']));

    }

    /**
     * Reset data
     */
    public function reset() {

        // call parent method
        parent::reset();

        // reset pointer
        $this->_file->seek(0);

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

        // read data
        $bytes = $this->_file->read($count);

        // get number of bytes read
        $bytesRead = strlen($bytes);

        // update archive position
        $this->_archive->incrementPosition($bytesRead);

        // mark end of content
        if ($this->_file->eof()) {
            $this->_state = Constants::STATE_ALMOST_DONE;
        }

        return $bytes;

    }

    /**
     * Event fired when reading starts
     */
    public function onStartReading() {

        // create file header
        $header  = ZipConstants::FILE_HEADER_SIGNATURE;
        $header .= ZipConstants::VERSION_MADE_BY;
        $header .= ZipConstants::VERSION_REQUIRED;
        $header .= ZipConstants::GENERAL_FLAGS;
        $header .= ZipConstants::COMPRESSION_METHOD;
        $header .= ZipConstants::LAST_MODIFIED_TIME;
        $header .= ZipConstants::LAST_MODIFIED_DATE;
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

        // add to archive headers
        $this->_archive->getCentralDirectoryHeaders()->add($header);

        // update end of central directory
        $centralDirectory = $this->_archive->getEndOfCentralDirectory();
        $centralDirectory->incrementAttribute('centralDirectorySize', strlen($header));
        $centralDirectory->incrementAttribute('centralDirectoryOffset', $this->_file->getSize());
        $centralDirectory->incrementAttribute('totalEntries', 1);
        $centralDirectory->incrementAttribute('diskEntries', 1);

    }

}