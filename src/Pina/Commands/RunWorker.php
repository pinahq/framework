<?php


namespace Pina\Commands;


use Pina\Command;
use Pina\Config;
use Pina\Events\Cron\CronEventWorker;

class RunWorker extends Command
{

    protected $workerId = null;
    protected $lockFile = null;

    public function __construct()
    {
        $this->config = Config::get('worker');
    }

    protected function execute($input = '')
    {
        if (!$this->lockFreeWorkedId()) {
            return;
        }

        $worker = new CronEventWorker();
        $worker->work(
            $this->workerId,
            isset($this->config['max_tasks']) ? $this->config['max_tasks'] : 10,
            isset($this->config['rest_seconds']) ? $this->config['rest_seconds'] : 10,
            isset($this->config['push_off_seconds']) ? $this->config['push_off_seconds'] : 300
        );

        $this->unlockWorkedId();
    }

    protected function lockFreeWorkedId()
    {
        $maxWorkers = isset($this->config['max_workers']) ? $this->config['max_workers'] : 1;
        for ($number = 1; $number <= $maxWorkers; $number ++) {
            $fp = fopen($this->getLockFile($number), "w+");
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                $this->workerId = $number;
                $this->lockFile = $fp;
                return true;
            }
            fclose($fp);
        }
        return false;
    }

    protected function unlockWorkedId()
    {
        if ($this->lockFile) {
            fclose($this->lockFile);
        }
        $this->workerId = null;
    }

    /**
     * @throws \Exception
     */
    protected function getLockFile($number)
    {
        $base = isset($this->config['lock']) ? $this->config['lock'] : '/tmp/pina-worker';
        if (empty($base)) {
            throw new \Exception('Please specify lock file path: cronLockFile in config/app.php');
        }
        return $base . $number;
    }

}