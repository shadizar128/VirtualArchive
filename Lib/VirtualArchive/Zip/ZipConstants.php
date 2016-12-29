<?php
namespace Lib\VirtualArchive\Zip;

class ZipConstants {

    // end of central directory signature
    const END_OF_CENTRAL_DIRECTORY_SIGNATURE = "\x50\x4b\x05\x06";

    // file signature
    const LOCAL_FILE_SIGNATURE = "\x50\x4b\x03\x04";

    // header signature
    const FILE_HEADER_SIGNATURE = "\x50\x4b\x01\x02";

    // header size
    const FILE_HEADER_SIZE = 46;

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

}