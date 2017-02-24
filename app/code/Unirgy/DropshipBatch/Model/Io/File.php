<?php

namespace Unirgy\DropshipBatch\Model\Io;

use Magento\Framework\Filesystem\Io\File as IoFile;
use Unirgy\DropshipBatch\Model\Io;

class File extends IoFile
{
    /**
     * @var Io
     */
    protected $_modelIo;

    public function __construct(Io $modelIo)
    {
        $this->_modelIo = $modelIo;

    }

    protected $_config = [];
    public function open(array $args = [])
    {
        $this->_config = $args;
        parent::open($args);
        if (!empty($this->_config['path'])) {
            $this->cd($this->_config['path']);
        }
    }
    
    public function mdtm($filename)
    {
        chdir($this->_cwd);
        $result = @filemtime($filename);
        chdir($this->_iwd);

        return $result;
    }
    
    public function isDir($filename)
    {
        $oldDir = $this->pwd();
        try {
            if ($this->cd($filename)) {
                $this->cd($oldDir);
                return true;
            }
        } catch (\Exception $e) {}
        return false;
    }

    public function filteredLs()
    {
        $result = parent::ls();
        return $this->_modelIo->filterLs($result, $this);
    }

    protected $_udbatchGrep;

    public function getUdbatchGrep()
    {
        $grep = @$this->_config['grep'];
        if (!is_null($this->_udbatchGrep)) {
            $grep = $this->_udbatchGrep;
        }
        return $grep;
    }
    public function setUdbatchGrep($grep)
    {
        $this->_udbatchGrep = $grep;
        return $this;
    }

    protected $_udbatch;

    public function getUdbatch()
    {
        return $this->_udbatch;
    }
    public function setUdbatch($udbatch)
    {
        $this->_udbatch = $udbatch;
        return $this;
    }

    public function createLocationString($filename, $protected=false)
    {
        $result = '';
        $result .= rtrim(@$this->_config['path'], DIRECTORY_SEPARATOR);
        $result .= DIRECTORY_SEPARATOR.$filename;
        return $result;
    }
}