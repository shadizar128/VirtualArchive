<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Exceptions\FileNotFoundException;
use Lib\VirtualArchive\Interfaces\IVirtualArchive;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

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
     * @var int Current position of the cursor from the beginning of the content
     */
    protected $_position = 0;

    /**
     * @var bool A flag to determine if the object has more content to be read
     */
    protected $_hasMoreContent = true;

    /**
     * Public constructor
     *
     * @param array $params A list of parameters
     * @throws FileNotFoundException
     */
    public function __construct(array $params) {

        $this->_centralDirectory = new CentralDirectory($params);
        $this->_centralDirectory->setArchive($this);

        $this->_headers = new Headers($params);
        $this->_headers->setArchive($this);

        $this->_files = new Files($params);
        $this->_files->setArchive($this);

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
     * Read $count bytes from the object
     *
     * @param int $count Number of bytes to read
     * @return string
     *
     */
    public function read($count) {

        $bytes = "";

        // read from files
        $bytesToRead = $count - strlen($bytes);
        if ($bytesToRead > 0) {
            $bytes .= $this->_files->read($bytesToRead);
        }

        // read from headers
        $bytesToRead = $count - strlen($bytes);
        if ($bytesToRead > 0) {
            $bytes .= $this->_headers->read($bytesToRead);
        }

        // read from central directory
        $bytesToRead = $count - strlen($bytes);
        if ($bytesToRead > 0) {
            $bytes .= $this->_centralDirectory->read($bytesToRead);
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

        if ($this->_hasMoreContent) {
            $this->_hasMoreContent =
                $this->_files->hasMoreContent() ||
                $this->_headers->hasMoreContent() ||
                $this->_centralDirectory->hasMoreContent();
        }

        return $this->_hasMoreContent;

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

}