<?php

namespace Tourze\Workerman\MasterKiller;

use Psr\Log\LoggerInterface;
use Workerman\Worker;

class MasterKiller
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function killMaster(): never
    {
        $master_pid = \is_file(Worker::$pidFile) ? (int)\file_get_contents(Worker::$pidFile) : 0;
        $sig = \SIGQUIT;
        // Send stop signal to master process.
        $master_pid && \posix_kill($master_pid, $sig);
        $this->logger->warning("Workerman[$master_pid] stop fail");
        // Timeout.
        $timeout = 5;
        $start_time = \time();
        // Check master process is still alive?
        while (1) {
            $master_is_alive = $master_pid && \posix_kill((int)$master_pid, 0);
            if ($master_is_alive) {
                // Timeout?
                if (\time() - $start_time >= $timeout) {
                    $this->logger->warning("Workerman stop fail");
                    exit;
                }
                // Waiting amoment.
                \usleep(10000);
                continue;
            }
            // Stop success.
            $this->logger->info("Workerman stop success");
            exit(0);
        }
    }
}
