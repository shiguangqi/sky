<?php
namespace Sky\Log;

class File extends \Sky\Log
{
    public $log_file;
    public function __construct($file)
    {
        if (is_string($file))
        {
            $conf = array('file' => $file);
        }
        if (isset($file['file']))
        {
            $this->log_file = $file['file'];
        }
        else
        {
            throw new \Exception(__CLASS__.": require \$conf[file]");
        }

        $this->fp = fopen($this->log_file, 'a+');
        if (!$this->fp)
        {
            throw new \Exception(__CLASS__.": can not open log_file[$this->log_file]");
        }
    }

    public function log($msg, $level = self::INFO)
    {
        $msg = $this->format($msg, $level);
        if ($msg) fputs($this->fp, $msg);
    }
}
