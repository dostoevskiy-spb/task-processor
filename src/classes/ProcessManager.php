<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 21.04.17
 * Time: 16:24
 */

namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\src\models\ProcessManager as ProcessManagerModel;
use yii\base\Object;

class ProcessManager extends Object
{
    public function signalHandler($signo, $pid = null, $status = null) {
        switch ($signo) {
            case SIGINT:
            case SIGTERM:
                //shutdown
                $pids = $this->getPids();
                foreach($pids as $pid) {
                    posix_kill($pid, SIGKILL);
                }
                break;
            case SIGHUP:
                //restart, not implemented
                break;
            case SIGUSR1:
                //user signal, not implemented
                break;
            case SIGCHLD:
//                if (!$pid) {
//                    $pid = pcntl_waitpid(-1, $status, WNOHANG);
//                }
//                while ($pid > 0) {
//                    if ($pid && isset(static::$currentJobs[$pid])) {
//                        unset(static::$currentJobs[$pid]);
//                    }
//                    $pid = pcntl_waitpid(-1, $status, WNOHANG);
//                }
                break;
        }
        die();
    }

    public function manage()
    {
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
//        pcntl_signal(SIGKILL, [$this, 'signalHandler']);
//        pcntl_signal(SIGSTOP, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        /**
         * Global Logic process manager (memory/process logic manager)
         */
        system('clear');

        /**
         * Impliments helper for notify
         *
         * @param \cli\Notify $notify
         *
         * @return \cli\Notify
         */
        function test_notify(\cli\Notify $notify)
        {
            return $notify;
        }

        /**
         * Cheack available RAM
         */
        $fh  = fopen('/proc/meminfo', 'r');
        $mem = 0;
        while ($line = fgets($fh)) {
            $pieces = [];
            if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
                $mem = $pieces[1];
                break;
            }
        }
        fclose($fh);

        /**
         * Process manager
         */
        $mem = ($mem / 1024) / 2; // mem to MB. We get (for now) just a half of free RAM.
//$workers = round($mem / 200); // Calculate number of workers
        $workers = 5;

        $nt = test_notify(new \cli\progress\Bar('Loading: ', $workers)); // Create 'ticker' instance

        for ($i = 1; $i <= $workers; $i++) {
            $nt->tick();
            exec('php ' . ROOT_DIR . '/yii dev/task-processor-run > /dev/null & echo $!');
            usleep(100000);
        }

        $headers = ['PID', 'CPU', 'Mem. (Resident)', 'Mem. (Virtual)'];
        while (true) {
            $data          = [];
            $cpuTotal      = [];
            $residentTotal = [];
            $virtTotal     = [];
            $pids = $this->getPids();
            foreach ($pids as $pid) {
                $rawOutput = exec("ps -p $pid -o %cpu,rss,vsz");
                $procInfo  = trim($rawOutput);
                $procInfo  = explode(' ', $procInfo);
                if ($procInfo[1] != '') {
                    $model = new ProcessManagerModel();
                    $model->pid = $pid;
                    $model->cpu = $procInfo[0];
                    $model->resident = $procInfo[1];
                    $model->virtual = $procInfo[2];
                    $data[]          = [$pid, $procInfo[0], $procInfo[1], $procInfo[2]];
                    $cpuTotal[]      = $procInfo[0];
                    $residentTotal[] = $procInfo[1];
                    $virtTotal[]     = $procInfo[2];
                } else {
                    $data[] = [$pid, 'HALT', 'HALT', 'HALT'];
                }

            }
            //$data[] = array('Total:', array_sum($cpuTotal) . '%', round(array_sum($residentTotal) / 1024, 2) . 'MB', round(array_sum($virtTotal) / 1024, 2) . 'MB');
            $str = ROOT_DIR . '/stats.json';
            file_put_contents($str, json_encode(['procs' => $data, 'totals' => ['Total:', array_sum($cpuTotal) . '%', round(array_sum($residentTotal) / 1024, 2) . 'MB', round(array_sum($virtTotal) / 1024, 2) . 'MB']]));
            system('clear');
            $table = new \cli\Table();
            $table->setFooters(['Total:', array_sum($cpuTotal) . '%', round(array_sum($residentTotal) / 1024, 2) . 'MB', round(array_sum($virtTotal) / 1024, 2) . 'MB']);
            $table->setHeaders($headers);
            $table->setRows($data);
            $table->display();
            unset($cpuTotal, $residentTotal, $virtTotal, $data);
            usleep(500000);
            pcntl_signal_dispatch();
        }
    }

    /**
     * @param $pids
     *
     * @return array
     */
    protected function getPids()
    {
        $pidsRaw    = shell_exec("ps axf | grep 'php ' | awk '{print $1\":\"$6$7}'");
        $pidsRawArr = explode("\n", $pidsRaw);
        $pids = [];
        foreach ($pidsRawArr as $pid) {
            if (stristr($pid, '/home/pavel/dev/www')) {
                $pdtmp      = explode(':', $pid);
                $pids[$pid] = $pdtmp[0];
            }
        }

        return $pids;
    }

}