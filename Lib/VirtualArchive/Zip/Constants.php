<?php
namespace Lib\VirtualArchive\Zip;

class Constants {

    // end of central directory signature
    const CENTRAL_DIR_SIGNATURE = "\x50\x4b\x05\x06";

    // file signature
    const FILE_SIGNATURE = "\x50\x4b\x03\x04";

    // header signature
    const HEADER_SIGNATURE = "\x50\x4b\x01\x02";

    // version made by
    const VERSION_MADE_BY = "\x14\x00";

    // version needed to extract
    const VERSION_REQUIRED = "\x14\x00";

    // general purpose bit flags
    const GENERAL_FLAGS = "\x00\x00";

    // compression method
    const COMPRESSION_METHOD = "\x08\x00";

    // last mod time
    const LAST_MODIFIED_TIME = "\x00\x00";

    // last mod date
    const LAST_MODIFIED_DATE = "\x00\x00";

    // file status
    const STATUS_NOT_STARTED    = 0x01;
    const STATUS_PROCESSING     = 0x02;
    const STATUS_ALMOST_DONE    = 0x03;
    const STATUS_DONE           = 0x04;

}