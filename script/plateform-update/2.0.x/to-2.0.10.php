<?php

require_once __DIR__."/../../../init.php";

mkdir(UPLOAD_CHUNK_DIRECTORY,0755,true);
chown(UPLOAD_CHUNK_DIRECTORY,DAEMON_USER);