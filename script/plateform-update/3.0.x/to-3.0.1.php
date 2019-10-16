<?php

require_once __DIR__ . '/../../../init.php';

if (!file_exists(HTML_PURIFIER_CACHE_PATH)) {
    mkdir(HTML_PURIFIER_CACHE_PATH, 0755, true);
}
chown(HTML_PURIFIER_CACHE_PATH, DAEMON_USER);