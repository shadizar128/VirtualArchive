<?php
namespace Lib\VirtualArchive;

class Constants {

    // file state
    const STATE_NOT_STARTED    = 0x01;
    const STATE_PROCESSING     = 0x02;
    const STATE_ALMOST_DONE    = 0x03;
    const STATE_DONE           = 0x04;

    // read buffer size
    const READ_BUFFER = 8192;

}