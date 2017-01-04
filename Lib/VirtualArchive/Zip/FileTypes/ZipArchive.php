<?php
namespace Lib\VirtualArchive\Zip\FileTypes;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Core\AbstractVirtualComponent;
use Lib\VirtualArchive\Core\FileTypes\VirtualFile;
use Lib\VirtualArchive\Exceptions\CentralDirectoryNotFound;
use Lib\VirtualArchive\Interfaces\IFile;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;
use Lib\VirtualArchive\Zip\Maps\CentralDirectoryHeaderMap;
use Lib\VirtualArchive\Zip\Maps\EndOfCentralDirectoryMap;
use Lib\VirtualArchive\Zip\ZipConstants;

class ZipArchive extends AbstractVirtualComponent implements IVirtualComponent {

    /**
     * @var IVirtualComponent
     */
    protected $_currentFile;

    /**
     * @var VirtualFile
     */
    protected $_centralDirectory;

    /**
     * @var VirtualFile
     */
    protected $_zipContent;

    /**
     * Class constructor.
     *
     * @param IFile $zip
     */
    public function __construct(IFile $zip) {

        // get end of central directory
        $endOfCentralDirectory = $this->_getEndOfCentralDirectory($zip);

        // get central directory offset
        $centralDirectoryOffset = EndOfCentralDirectoryMap::getAttribute($endOfCentralDirectory, 'centralDirectoryOffset');

        // set central directory
        $this->_centralDirectory = new VirtualFile($zip, $centralDirectoryOffset, $zip->getSize() - strlen($endOfCentralDirectory));

        // set zip content
        $this->_zipContent = new VirtualFile($zip, 0, $centralDirectoryOffset);

    }

    /**
     * Search for the end of central directory and return its content
     *
     * @param IFile $zip
     * @return string
     * @throws CentralDirectoryNotFound
     */
    protected function _getEndOfCentralDirectory(IFile $zip) {

        // get archive size
        $fileSize = $zip->getSize();

        // search the archive in batches from end to start
        $searchPosition = max(0, $fileSize - Constants::READ_BUFFER);

        // keep the first bytes of the previous batch
        // because they might contain part of the signature
        $previousBytes = "";

        while ($searchPosition >= 0) {

            // move read pointer
            $zip->seek($searchPosition);

            // read batch bytes
            $bytes = $zip->read(Constants::READ_BUFFER);

            // append the first bytes of the last batch
            $bytes .= $previousBytes;

            // get relative position
            $relativePosition = strpos($bytes, ZipConstants::END_OF_CENTRAL_DIRECTORY_SIGNATURE);

            if ($relativePosition !== FALSE) {

                // compute absolute position
                $absolutePosition = $searchPosition + $relativePosition;

                // move read pointer
                $zip->seek($absolutePosition);

                // return the end of central directory
                return $zip->read($fileSize - $absolutePosition);

            }

            // keep the first bytes of the current batch
            $previousBytes = substr($bytes, 0, strlen(ZipConstants::END_OF_CENTRAL_DIRECTORY_SIGNATURE));

            // move search position to next batch
            $searchPosition = $searchPosition - Constants::READ_BUFFER;

        }

        // if this point is reached then the end of central directory was not found
        throw new CentralDirectoryNotFound();

    }

    /**
     * Reset data
     */
    public function reset() {

        // call parent method
        parent::reset();

        // reset read pointer
        $this->_centralDirectory->seek(0);

        // reset read pointer
        $this->_zipContent->seek(0);

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
                if ($this->_currentFile->eof()) {

                    // unset current file
                    $this->_currentFile = null;

                }

            } else {
                $this->_state = Constants::STATE_ALMOST_DONE;
                break;
            }

        }

        return $bytes;

    }

    /**
     * Get next file
     */
    protected function _getNextFile() {

        $header = $this->_getNextHeader();
        if (!$header) {
            return null;
        }

        $fileSize = CentralDirectoryHeaderMap::getAttribute($header, 'compressedSize')
            + CentralDirectoryHeaderMap::getAttribute($header, 'fileNameLength')
            + CentralDirectoryHeaderMap::getAttribute($header, 'extraFieldLength')
            + ZipConstants::LOCAL_FILE_HEADER_SIZE;

        $fileOffset = CentralDirectoryHeaderMap::getAttribute($header, 'localFileRelativeOffset');

        // change relative file position and disk
        CentralDirectoryHeaderMap::setAttribute($header, 'diskNumberWhereFileStarts', 1);
        CentralDirectoryHeaderMap::setAttribute($header, 'localFileRelativeOffset', $this->_archive->getPosition());

        // add to archive headers
        $this->_archive->getCentralDirectoryHeaders()->add($header);

        // update end of central directory
        $centralDirectory = $this->_archive->getEndOfCentralDirectory();
        $centralDirectory->incrementAttribute('centralDirectorySize', strlen($header));
        $centralDirectory->incrementAttribute('centralDirectoryOffset', $fileSize);
        $centralDirectory->incrementAttribute('totalEntries', 1);
        $centralDirectory->incrementAttribute('diskEntries', 1);

        return new VirtualFile($this->_zipContent, $fileOffset, $fileOffset + $fileSize);

    }

    /**
     * Get next file header
     *
     * @return null|string
     */
    protected function _getNextHeader() {

        // read default header data
        $header = $this->_centralDirectory->read(ZipConstants::FILE_HEADER_SIZE);

        // check signature
        $signature = CentralDirectoryHeaderMap::getAttribute($header, 'headerSignature');

        // signature not found means there is no next header
        if ($signature != ZipConstants::FILE_HEADER_SIGNATURE) {
            return null;
        }

        // compute extra header size
        $readExtraBytes = CentralDirectoryHeaderMap::getAttribute($header, 'fileNameLength') +
            CentralDirectoryHeaderMap::getAttribute($header, 'extraFieldLength') +
            CentralDirectoryHeaderMap::getAttribute($header, 'fileCommentLength');

        // read extra header data
        $header .= $this->_centralDirectory->read($readExtraBytes);

        // return header
        return $header;

    }

}