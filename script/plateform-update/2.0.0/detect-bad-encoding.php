<?php

require_once __DIR__ . "/../../../init.php";


$filesystem = new \Symfony\Component\Finder\Finder();

$files = $filesystem->files()->in(WORKSPACE_PATH)->name("*.yml");

$fileInfo = new finfo();
/** @var SplFileInfo $file */
foreach ($files as $file) {
    $encoding = $fileInfo->file($file->getRealPath(), FILEINFO_MIME_ENCODING);
    if ($encoding != 'unknown-8bit') {
        continue;
    }
    echo $file->getRealPath() . " : " . $encoding ;

    $file_content = file_get_contents($file->getRealPath());

    $file_content = iconv('WINDOWS-1252', 'UTF-8', $file_content);
    file_put_contents($file->getRealPath(), $file_content);
    echo " - OK\n";
}
