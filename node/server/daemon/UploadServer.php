<?php
namespace Sky;

require_once __DIR__."/../utils/libs.php";
class UploadServer
{
    /**
     * @var swoole_serverer
     */
    protected $server;
    protected $files;

    protected $root_path = '/tmp/';
    protected $override = false;

    static $max_file_size = 100000000; //100M

    private $pid;
    private $pid_file;

    public $loger;

    function __construct($config)
    {
        $this->config = $config;
        $this->pid_file = $config['pid'];
        $this->root_path = rtrim($this->root_path, ' /');
    }

    public function setLoger($log)
    {
        $this->loger = $log;
    }

    public function log($msg)
    {
        $this->loger->log($msg);
    }

    function onConnect($server, $fd, $from_id)
    {
        $this->log("new upload client[$fd] connected.\n");
    }

    function message($fd, $code, $msg)
    {
        $this->server->send($fd, json_encode(array('code' => $code, 'msg' => $msg)));
        $this->log("[-->$fd]\t$code\t$msg");
        if ($code != 0) {
            $this->server->close($fd);
        }
        return true;
    }

    function onReceive($server, $fd, $from_id, $data)
    {
        //传输尚未开始
        if (empty($this->files[$fd])) {
            $req = json_decode($data, true);
            if ($req === false) {
                return $this->message($fd, 400, 'Error Request');
            } elseif (empty($req['size']) or empty($req['name'])) {
                return $this->message($fd, 500, 'require file name and size.');
            } elseif ($req['size'] > self::$max_file_size) {
                return $this->message($fd, 501, 'over the max_file_size. ' . self::$max_file_size);
            }
            $file = $this->root_path . '/' . $req['name'];
            $dir = realpath(dirname($file));
            if (!$dir or strncmp($dir, $this->root_path, strlen($this->root_path)) != 0) {
                return $this->message($fd, 502, "file path[$dir] error. Access deny.");
            } elseif ($this->override and is_file($file)) {
                return $this->message($fd, 503, 'file exists. serverer not allowed override');
            }
            $fp = fopen($file, 'w');
            if (!$fp) {
                return $this->message($fd, 504, 'can open file.');
            } else {
                $this->message($fd, 0, 'transmission start');
                $this->files[$fd] = array('fp' => $fp, 'name' => $file, 'size' => $req['size'], 'recv' => 0);
            }
        } //传输已建立
        else {
            $info = & $this->files[$fd];
            $fp = $info['fp'];
            $file = $info['name'];
            if (!fwrite($fp, $data)) {
                $this->message($fd, 600, "fwrite failed. transmission stop.");
                unlink($file);
            } else {
                $info['recv'] += strlen($data);
                if ($info['recv'] >= $info['size']) {
                    $this->message($fd, 0, "Success, transmission finish. Close connection.");
                    unset($this->files[$fd]);
                }
            }
        }
    }

    function onMasterStart($server)
    {
        global $argv;
        setProcessName("{$argv[0]} [upload_server] : master -host= {$this->config['host']} -port={$this->config['port']}");
        $this->log("upload server start");
        $this->pid = $server->master_pid;
        file_put_contents($this->pid_file,$this->pid);
    }

    function onManagerStart($server)
    {
        global $argv;
        setProcessName("{$argv[0]} [upload_server] : manager");
    }

    function onWorkerStart($server)
    {
        global $argv;
        setProcessName("{$argv[0]} [upload_server] : worker");
    }

    function onManagerStop($server)
    {
        $this->log("upload server stop");
        if (file_exists($this->pid_file))
            unlink($this->pid_file);
    }

    function onclose($server, $fd, $from_id)
    {
        unset($this->files[$fd]);
        $this->log("upload client[$fd] closed.");
    }

    function start()
    {
        $server = new \swoole_server($this->config['host'], $this->config['port']);
        $server->set(array(
            'worker_num'=>1,
            'daemonize'=>1
        ));
        $server->on('Start', array($this,'onMasterStart'));
        $server->on('ManagerStart', array($this, 'onManagerStart'));
        $server->on('workerStart', array($this, 'onWorkerStart'));
        $server->on('managerStop', array($this,'onManagerStop'));
        $server->on('connect', array($this, 'onConnect'));
        $server->on('receive', array($this, 'onreceive'));
        $server->on('close', array($this, 'onclose'));
        $this->server = $server;
        $server->start();
    }

    function stop()
    {
        $this->server->shutdown();
    }
}
