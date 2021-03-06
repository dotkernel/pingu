<?php

declare(strict_types=1);

namespace Dot\Swoole;

use Dot\Swoole\Exception\RuntimeException;
use function file_put_contents;
use function file_get_contents;
use function sprintf;
use function explode;
use function is_readable;
use function is_writable;
use function unlink;

class PidManager
{
    /**
     * @var string
     */
    private $pidFile = '';

    public function __construct(string $pidFile)
    {
        $this->pidFile = $pidFile;
    }

    /**
     * Write master pid and manager pid to pid file
     *
     * @throws RuntimeException When $pidFile is not writable
     */
    public function write(int $masterPid, int $managerPid) : void
    {
        if (! is_writable($this->pidFile) && ! is_writable(dirname($this->pidFile))) {
            throw new RuntimeException(sprintf('Pid file "%s" is not writable', $this->pidFile));
        }
        file_put_contents($this->pidFile, $masterPid . ',' . $managerPid);
    }

    /**
     * Read master pid and manager pid from pid file
     *
     * @return string[] {
     *     @var string $masterPid
     *     @var string $managerPid
     * }
     */
    public function read() : array
    {
        $pids = [];
        if (is_readable($this->pidFile)) {
            $content = file_get_contents($this->pidFile);
            $pids = explode(',', $content);
        }
        return $pids;
    }

    /**
     * Delete pid file
     */
    public function delete() : bool
    {
        if (is_writable($this->pidFile)) {
            return unlink($this->pidFile);
        }
        return false;
    }
}
