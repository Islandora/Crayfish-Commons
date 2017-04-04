<?php

namespace Islandora\Crayfish\Commons;

use Psr\Log\LoggerInterface;

/**
 * Runs a command streaming data in on stdin and out on stdout.
 *
 * @package Islandora\Crayfish\Commons
 */
class CmdExecuteService
{

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * Executor constructor.
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log = null)
    {
        $this->log = $log;
    }

    /**
     * Runs the command
     *
     * @param $cmd
     * @param $data
     *
     * @throws \RuntimeException
     *
     * @return \Closure
     *   Closure that streams the output of the command.
     */
    public function execute($cmd, $data)
    {
        // Use pipes for STDIN, STDOUT, and STDERR
        $descr = array(
          0 => array(
            'pipe',
            'r'
          ) ,
          1 => array(
            'pipe',
            'w'
          ) ,
          2 => array(
            'pipe',
            'w'
          )
        );
        $pipes = [];

        // Start process, telling it to use STDIN for input and STDOUT for output.
        $cmd = escapeshellcmd($cmd);
        $process = proc_open($cmd, $descr, $pipes);

        // Stream input to STDIN
        stream_copy_to_stream($data, $pipes[0]);

        // Close STDIN
        fclose($pipes[0]);

        // Wait for process to finish and get its exit code
        $exit_code = null;
        while ($exit_code === null) {
            $status = proc_get_status($process);
            if ($status['running'] === false) {
                $exit_code = $status['exitcode'];
            }
        }

        // On error, extract message from STDERR and throw an exception.
        if ($exit_code != 0) {
            $msg = stream_get_contents($pipes[2]);
            $this->cleanup($pipes, $process);
            if ($this->log) {
                $this->log->error('Process exited with non-zero code.', [
                  'exit_code' => $exit_code,
                  'stderr' => $msg,
                ]);
            }
            throw new \RuntimeException($msg, 500);
        }

        // Return a function that streams the output.
        return function () use ($pipes, $process) {
            // Flush output
            while ($chunk = fread($pipes[1], 1024)) {
                echo $chunk;
                ob_flush();
                flush();
            }

            $this->cleanup($pipes, $process);
        };
    }

    protected function cleanup($pipes, $process)
    {
        // Close STDOUT and STDERR
        for ($i = 1; $i < count($pipes); $i++) {
            fclose($pipes[$i]);
        }

        // Close the process;
        proc_close($process);
    }
}
