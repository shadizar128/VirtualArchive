<?php
namespace Lib\VirtualArchive;

class Constants {

    // file status
    const STATUS_NOT_STARTED    = 0x01;
    const STATUS_PROCESSING     = 0x02;
    const STATUS_ALMOST_DONE    = 0x03;
    const STATUS_DONE           = 0x04;

    // read buffer size
    const READ_BUFFER = 8192;

}