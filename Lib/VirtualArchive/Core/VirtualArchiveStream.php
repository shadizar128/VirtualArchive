<?php
namespace Lib\VirtualArchive\Core;
use Lib\VirtualArchive\Interfaces\IVirtualArchive;

class VirtualArchiveStream {

    /**
     * @var string Stream name
     */
    protected $_streamName = 'VirtualArchiveStream';

    /**
     * @var resource The stream context
     */
    protected $context;

    /**
     * @var IVirtualArchive the internal virtual zip object
     */
    protected $_archive;

    /**
     * Get a virtual archive as a stream
     *
     * @param IVirtualArchive $archive
     * @return resource
     */
    public function getStream($archive) {

        if (!in_array($this->_streamName, stream_get_wrappers())) {
            stream_wrapper_register($this->_streamName, get_called_class());
        }

        $context = stream_context_create(
            array(
                $this->_streamName => array(
                    'archive' => $archive
                )
            )
        );

        return fopen($this->_streamName . '://', 'r', false, $context);

    }

    /**
     * Open stream
     *
     * @param string $path Specifies the URL that was passed to the original function
     * @param string $mode The mode used to open the file, as detailed for fopen()
     * @param int $options Holds additional flags set by the streams API
     * @param string $opened_path
     * @return bool TRUE on success or FALSE on failure.
     */
    public function stream_open($path, $mode, $options, &$opened_path) {

        // get context
        $context = stream_context_get_options($this->context);

        // get archive
        $this->_archive = $context[$this->_streamName]['archive'];

        return true;

    }

    /**
     * Read from stream
     *
     * @param int $count How many bytes of data from the current position should be returned
     * @return string If there are less than count bytes available, return as many as are available.
     *                If no more data is available, return an empty string.
     */
    function stream_read($count) {
        return $this->_archive->read($count);
    }

    /**
     * Retrieve the current position of a stream
     *
     * @return int
     */
    function stream_tell() {
        return $this->_archive->getPosition();
    }

    /**
     * Returns true if end of stream
     *
     * @return bool
     */
    function stream_eof() {
        return $this->_archive->hasMoreContent();
    }

}