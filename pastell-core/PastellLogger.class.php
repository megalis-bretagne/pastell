<?php

use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\OutputInterface;

class PastellLogger
{
    public const MESSAGE = 'message';

    private Logger $logger;
    private int $log_level;
    private ?string $name = null;

    public function __construct(Logger $logger, int $log_level = Logger::INFO)
    {
        $this->logger = $logger;
        $this->log_level = $log_level;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function debug($message, array $context = []): void
    {
        $this->getLoggerWithName()->debug($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->getLoggerWithName()->info($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->getLoggerWithName()->notice($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->getLoggerWithName()->warning($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->getLoggerWithName()->error($message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->getLoggerWithName()->alert($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->getLoggerWithName()->critical($message, $context);
    }

    public function emergency($message, array $context = []): void
    {
        $this->getLoggerWithName()->emergency($message, $context);
    }

    public function enableStdOut(bool $enable_stdout = true): void
    {
        if (! $enable_stdout) {
            return;
        }
        try {
            $handler = new  Monolog\Handler\StreamHandler('php://stdout', $this->log_level);
            $this->logger->pushHandler($handler);
        } catch (Exception $e) {
            $message =  "Impossible de créer un streamHandler sur sdtout : " . $e->getMessage();
            echo $message;
            $this->critical($message, [$e]);
        }
    }

    private function getLoggerWithName(): Logger
    {
        if ($this->name === null) {
            $trace = debug_backtrace();
            if (empty($trace[2]['class'])) {
                $this->name = basename($trace[1]['file']);
            } else {
                $this->name = $trace[2]['class'];
            }
        }
        return $this->logger->withName($this->name);
    }

    public function enableConsoleHandler(OutputInterface $output): void
    {
        $consoleHandler = new ConsoleHandler($output);
        $this->logger->pushHandler($consoleHandler);
    }
}
