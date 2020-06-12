<?php

declare(strict_types=1);

namespace Dot\Swoole\Command;

use Swoole\Process as SwooleProcess;
use Dot\Swoole\PidManager;

trait IsRunningTrait
{
    /**
     * Is the swoole  server running?
     */
    public function isRunning() : bool
    {
        $pids = $this->pidManager->read();

        if ([] === $pids) {
            return false;
        }

        [$masterPid, $managerPid] = $pids;

        if ($managerPid) {
            // Swoole process mode
            return $masterPid && $managerPid && SwooleProcess::kill((int) $managerPid, 0);
        }

        // Swoole base mode, no manager process
        return $masterPid && SwooleProcess::kill((int) $masterPid, 0);
    }
}
