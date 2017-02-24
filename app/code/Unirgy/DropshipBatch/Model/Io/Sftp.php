<?php

namespace Unirgy\DropshipBatch\Model\Io;

use Magento\Framework\Filesystem\Io\Sftp as IoSftp;
use Unirgy\DropshipBatch\Model\Io;

class Sftp extends IoSftp
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
        return $this->_connection->mdtm($filename);
    }
    public function isDir($filename)
    {
        $oldDir = $this->pwd();
        if ($filename[strlen($filename) - 1] != '/') {
            $filename.= '/';
        }
        if ($this->_connection->_realpath($filename) && $this->cd($filename)) {
            $this->cd($oldDir);
            return true;
        } else {
            return false;
        }
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
        if (0 === strpos($filename, './')) {
            $filename = substr($filename, 2);
        }
        $pass = !$protected ? @$this->_config['pass'] : '***';
        return sprintf('%s://%s:%s@%s%s%s',
            @$this->_config['scheme'], @$this->_config['user'], $pass,
            @$this->_config['host'], rtrim(@$this->_config['path'], '/'),
            '/'.$filename
        );
    }

}