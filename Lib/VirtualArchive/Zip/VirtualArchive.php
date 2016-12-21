<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Exceptions\FileNotFoundException;
use Lib\VirtualArchive\Interfaces\IVirtualArchive;

/**
 * Class that creates a virtual zip archive from other files without creating a new file on disk or storing the data in memory.
 * Inspired by this code: http://www.granthinkson.com/2005/07/01/create-zip-files-dynamically-using-php/
 * Zip file structure: https://users.cs.jmu.edu/buchhofp/forensics/formats/pkzip.html
 */
class VirtualArchive implements IVirtualArchive, IVirtualComponent {

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
     * @var array Current position of the cursor from the begining of the archive
     */
    protected $_position = 0;

    /**
     * @var bool A flag to determine if the archive has more content to be read
     */
    protected $_hasMoreContent = true;

    /**
     * Public constructor
     *
     * @param array $params A list of parameters
     * @throws FileNotFoundException
     */
    public function __construct(array $params) {

        $this->_centralDirectory = new CentralDirectory();
        $this->_headers = new Headers();
        $this->_files = new Files($params);

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

        $this->_centralDirectory->reset();
        $this->_headers->reset();
        $this->_files->reset();

        $this->_position = 0;
        $this->_hasMoreContent = true;

    }

    /**
     * Read $count bytes from the archive, null given when eof
     *
     * @param int $count Number of bytes to read
     * @return null|string
     *
     */
    public function read($count) {

        // result
        $bytes = "";

        // flag to indicate the end of line
        $endOfFile = true;

        // read from the original file until the start of central directory
        $bytesToRead = min($count, max(0, $this->_bytes['disk'] - $this->_pointers['disk']));
        if ($bytesToRead > 0) {
            $bytes .= fread($this->_fileHandle, $bytesToRead);
            $count -= $bytesToRead;
            $this->_pointers['disk'] += $bytesToRead;
            $endOfFile = false;
        }

        // if more bytes left to read
        if ($count > 0) {

            // read from the additional fires
            $bytesToRead = min($count, max(0, $this->_bytes['memory'] - $this->_pointers['memory']));
            if ($bytesToRead > 0) {
                $bytes .= substr($this->_additionalData, $this->_pointers['memory'], $bytesToRead);
                $count -= $bytesToRead;
                $this->_pointers['memory'] += $bytesToRead;
                $endOfFile = false;
            }

        }

        // if more bytes left to read
        if ($count > 0) {

            // read from the central directory headers
            $bytesToRead = min($count, max(0, $this->_bytes['cdrHeaders'] - $this->_pointers['cdrHeaders']));
            if ($bytesToRead > 0) {
                $bytes .= substr($this->_centralDirectoryHeaders, $this->_pointers['cdrHeaders'], $bytesToRead);
                $count -= $bytesToRead;
                $this->_pointers['cdrHeaders'] += $bytesToRead;
                $endOfFile = false;
            }

        }

        // if more bytes left to read
        if ($count > 0) {

            // read from the end of central directory
            $bytesToRead = min($count, max(0, $this->_bytes['cdrEnd'] - $this->_pointers['cdrEnd']));
            if ($bytesToRead > 0) {
                $bytes .= substr($this->_centralDirectoryEnd, $this->_pointers['cdrEnd'], $bytesToRead);
                $this->_pointers['cdrEnd'] += $bytesToRead;
                $endOfFile = false;
            }

        }

        // return bytes read or null if end of file
        return $endOfFile ? false : $bytes;

    }

    /**
     * Get cursor position
     *
     * @return int
     */
    public function getPosition() {
        return array_sum($this->_position);
    }

    /**
     * Return true if the archive has more content
     *
     * @return bool
     */
    public function hasMoreContent() {

        if ($this->_hasMoreContent) {
            $this->_hasMoreContent = $this->_files->hasMoreContent() && $this->_headers->hasMoreContent() && $this->_centralDirectory->hasMoreContent();
        }

        return $this->_hasMoreContent;

    }

}