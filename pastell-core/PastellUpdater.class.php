<?php

class PastellUpdater
{
    private $pastellLogger;

    public function __construct(PastellLogger $pastellLogger)
    {
        $this->pastellLogger = $pastellLogger;
    }

    public function update()
    {
        $this->to301();
    }

    public function to301()
    {
        $this->pastellLogger->info('Start script to 3.0.1');
        if (!file_exists(HTML_PURIFIER_CACHE_PATH)) {
            mkdir(HTML_PURIFIER_CACHE_PATH, 0755, true);
        }
        chown(HTML_PURIFIER_CACHE_PATH, DAEMON_USER);
        $this->pastellLogger->info('End script to 3.0.1');
    }
}