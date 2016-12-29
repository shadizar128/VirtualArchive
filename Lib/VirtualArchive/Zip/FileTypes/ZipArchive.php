<?php
namespace Lib\VirtualArchive\Zip\FileTypes;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Core\FileTypes\DiskFile;
use Lib\VirtualArchive\Core\FileTypes\PartialDiskFile;
use Lib\VirtualArchive\Interfaces\IFile;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;
use Lib\VirtualArchive\Zip\Maps\CentralDirectoryHeaderMap;
use Lib\VirtualArchive\Zip\ZipConstants;

class ZipArchive extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var string
     */
    protected $_content;

    /**
     * @var CentralDirectoryHeaders
     */
    protected $_centralDirectoryHeaders;

    /**
     * @var IVirtualComponent
     */
    protected $_currentFile;

    /**
     * Class constructor.
     *
     * @param IFile $archive
     */
    public function __construct(IFile $archive) {

        // get headers
        $this->_centralDirectoryHeaders = new CentralDirectoryHeaders($archive);

    }

    /**
     * Reset data
     */
    public function reset() {

        // call parent method
        parent::reset();

        // reset central directory headers
        $this->_centralDirectoryHeaders->reset();

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

        $header = $this->_centralDirectoryHeaders->getNextHeader();
        if (!$header) {
            return null;
        }


        $file = new CompressedFile();



        // change relative file position and disk
        CentralDirectoryHeaderMap::setAttribute($header, 'diskNumberWhereFileStarts', 1);
        CentralDirectoryHeaderMap::setAttribute($header, 'localFileRelativeOffset', $this->_archive->getPosition());

        // add to archive headers
        $this->_archive->getCentralDirectoryHeaders()->add($header);

        // update end of central directory
        $centralDirectory = $this->_archive->getEndOfCentralDirectory();
        $centralDirectory->incrementAttribute('centralDirectorySize', strlen($header));
        $centralDirectory->incrementAttribute('centralDirectoryOffset', $this->_file->getSize());
        $centralDirectory->incrementAttribute('totalEntries', 1);
        $centralDirectory->incrementAttribute('diskEntries', 1);

        return $file;

    }

}