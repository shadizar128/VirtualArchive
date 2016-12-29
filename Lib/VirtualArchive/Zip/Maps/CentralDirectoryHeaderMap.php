<?php
namespace Lib\VirtualArchive\Zip\Maps;

class CentralDirectoryHeaderMap extends AbstractZipMap {

    /**
     * @var array Central directory header map
     */
    protected static $_map = array(
        'headerSignature' => array(
            'offset' => 0,
            'length' => 4,
            'method'  => null
        ),
        'versionMadeBy' => array(
            'offset' => 4,
            'length' => 2,
            'method'  => 'v'
        ),
        'versionNeeded' => array(
            'offset' => 6,
            'length' => 2,
            'method'  => 'v'
        ),
        'generalPurposeFlag' => array(
            'offset' => 8,
            'length' => 2,
            'method'  => 'v'
        ),
        'compressionMethod' => array(
            'offset' => 10,
            'length' => 2,
            'method'  => 'v'
        ),
        'lastModificationTime' => array(
            'offset' => 12,
            'length' => 2,
            'method'  => 'v'
        ),
        'lastModificationDate' => array(
            'offset' => 14,
            'length' => 2,
            'method'  => 'v'
        ),
        'crc32' => array(
            'offset' => 16,
            'length' => 4,
            'method'  => 'V'
        ),
        'compressedSize' => array(
            'offset' => 20,
            'length' => 4,
            'method'  => 'V'
        ),
        'uncompressedSize' => array(
            'offset' => 24,
            'length' => 4,
            'method'  => 'V'
        ),
        'fileNameLength' => array(
            'offset' => 28,
            'length' => 2,
            'method'  => 'v'
        ),
        'extraFieldLength' => array(
            'offset' => 30,
            'length' => 2,
            'method'  => 'v'
        ),
        'fileCommentLength' => array(
            'offset' => 32,
            'length' => 2,
            'method'  => 'v'
        ),
        'diskNumberWhereFileStarts' => array(
            'offset' => 34,
            'length' => 2,
            'method'  => 'v'
        ),
        'internalFileAttributes' => array(
            'offset' => 36,
            'length' => 2,
            'method'  => 'v'
        ),
        'externalFileAttributes' => array(
            'offset' => 38,
            'length' => 4,
            'method'  => 'V'
        ),
        'localFileRelativeOffset' => array(
            'offset' => 42,
            'length' => 4,
            'method'  => 'V'
        )
    );

}