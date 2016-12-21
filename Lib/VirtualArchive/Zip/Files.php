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
     * Class constructor.
     * @param array $params
     */
    public function __construct(array $params) {

    }

    /**
     * Reset all data
     */
    public function reset() {

    }

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent() {
        return false;
    }

}