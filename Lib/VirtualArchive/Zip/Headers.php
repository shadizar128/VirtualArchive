<?php
namespace Lib\VirtualArchive\Zip;
use Lib\VirtualArchive\Interfaces\IVirtualComponent;

class Headers implements IVirtualComponent {

    /**
     * @var string Archive file headers
     */
    protected $_data;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->reset();
    }

    /**
     * Reset all data
     */
    public function reset() {
        $this->_data = "";
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