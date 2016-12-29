<?php
namespace Lib\VirtualArchive\Zip\FileTypes;
use Lib\VirtualArchive\Constants;
use Lib\VirtualArchive\Exceptions\CentralDirectoryNotFound;
use Lib\VirtualArchive\Interfaces\IFile;
use Lib\VirtualArchive\Zip\Maps\CentralDirectoryHeaderMap;
use Lib\VirtualArchive\Zip\Maps\EndOfCentralDirectoryMap;
use Lib\VirtualArchive\Zip\ZipConstants;

class CentralDirectoryHeaders {

    /**
     * @var IFile The archive
     */
    protected $_archive;

    /**
     * @var int Offset of start of central directory, relative to start of archive
     */
    protected $_centralDirectoryOffset;

    /**
     * @var int Total number of central directory records
     */
    protected $_totalEntries;

    /**
     * @var int Current central directory record number
     */
    protected $_pointer;

    /**
     * Class constructor.
     * @param IFile $archive
     */
    public function __construct(IFile $archive) {

        // set archive
        $this->_archive = $archive;

        // get central directory
        $endOfCentralDirectory = $this->_getEndOfCentralDirectory();

        // get total number of entries
        $this->_totalEntries = EndOfCentralDirectoryMap::getAttribute($endOfCentralDirectory, 'totalEntries');

        // get central directory offset
        $this->_centralDirectoryOffset = EndOfCentralDirectoryMap::getAttribute($endOfCentralDirectory, 'centralDirectoryOffset');

    }

    /**
     * Search for the end of central directory and return its content
     *
     * @return string
     * @throws CentralDirectoryNotFound
     */
    protected function _getEndOfCentralDirectory() {

        // get archive size
        $fileSize = $this->_archive->getSize();

        // search the archive in batches from end to start
        $searchPosition = max(0, $fileSize - Constants::READ_BUFFER);

        // keep the first bytes of the previous batch
        // because they might contain part of the signature
        $previousBytes = "";

        while ($searchPosition >= 0) {

            // move read pointer
            $this->_archive->seek($searchPosition);

            // read batch bytes
            $bytes = $this->_archive->read(Constants::READ_BUFFER);

            // append the first bytes of the last batch
            $bytes .= $previousBytes;

            // get relative position
            $relativePosition = strpos($bytes, ZipConstants::END_OF_CENTRAL_DIRECTORY_SIGNATURE);

            if ($relativePosition !== FALSE) {

                // compute absolute position
                $absolutePosition = $searchPosition + $relativePosition;

                // move read pointer
                $this->_archive->seek($absolutePosition);

                // return the end of central directory
                return $this->_archive->read($fileSize - $absolutePosition);

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
     * Get next file header
     *
     * @return null|string
     * @throws \Exception
     */
    public function getNextHeader() {

        // check if more entries
        if ($this->_pointer >= $this->_totalEntries) {
            return null;
        }

        // increment pointer
        $this->_pointer++;

        // read default header data
        $header = $this->_archive->read(ZipConstants::FILE_HEADER_SIZE);

        // check signature
        $signature = CentralDirectoryHeaderMap::getAttribute($header, 'headerSignature');

        // signature not found means there is no next header
        if ($signature != ZipConstants::FILE_HEADER_SIGNATURE) {
            throw new \Exception('Invalid central directory record');
        }

        // compute extra header size
        $readExtraBytes = CentralDirectoryHeaderMap::getAttribute($header, 'fileNameLength') +
                          CentralDirectoryHeaderMap::getAttribute($header, 'extraFieldLength') +
                          CentralDirectoryHeaderMap::getAttribute($header, 'fileCommentLength');

        // read extra header data
        $header .= $this->_archive->read($readExtraBytes);

        // return header
        return $header;

    }

    /**
     * Reset data
     */
    public function reset() {

        // reset pointer
        $this->_pointer = 0;

        // reset read pointer
        $this->_archive->seek($this->_centralDirectoryOffset);

    }

}