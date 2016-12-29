<?php
namespace Lib\VirtualArchive\Zip\Maps;

class EndOfCentralDirectoryMap extends AbstractZipMap {

    /**
     * @var array End of central directory map
     */
    protected static $_map = array(
        'diskEntries' => array(
            'offset' => 8,
            'length' => 2,
            'method'  => 'v'
        ),
        'totalEntries' => array(
            'offset' => 10,
            'length' => 2,
            'method'  => 'v'
        ),
        'centralDirectorySize' => array(
            'offset' => 12,
            'length' => 4,
            'method'  => 'V'
        ),
        'centralDirectoryOffset' => array(
            'offset' => 16,
            'length' => 4,
            'method'  => 'V'
        )
    );

}