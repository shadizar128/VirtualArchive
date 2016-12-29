<?php
namespace Lib\VirtualArchive\Interfaces;

interface IZipHeaders {

    public function reset();

    public function getNext();

}