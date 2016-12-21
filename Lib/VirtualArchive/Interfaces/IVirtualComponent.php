<?php
namespace Lib\VirtualArchive\Interfaces;

interface IVirtualComponent {

    /**
     * Return true if object has more content and false otherwise
     *
     * @return bool
     */
    public function hasMoreContent();

}