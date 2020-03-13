<?php

namespace Pastell\Updater;

class Version301 implements Version
{
    public function update(): void
    {
        if (!file_exists(HTML_PURIFIER_CACHE_PATH)) {
            mkdir(HTML_PURIFIER_CACHE_PATH, 0755, true);
        }
        chown(HTML_PURIFIER_CACHE_PATH, DAEMON_USER);
    }
}
