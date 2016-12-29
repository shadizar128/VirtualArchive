<?php
namespace Lib\VirtualArchive\Interfaces;

interface IZipArchive {

    function _getCentralDirectory();

    function _getNextFile() {

    }

}