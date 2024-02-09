<?php

namespace Pastell\Connector\Ensap;

use RuntimeException;

use function extension_loaded;

class GPGEncryptor
{
    public function encryptArchive($archivePath, $outputPath, $publicKey): void
    {
        if (!extension_loaded('gnupg')) {
            throw new RuntimeException('The gnupg extension is not loaded.');
        }

        if (!file_exists($archivePath)) {
            throw new RuntimeException('The archive file does not exist.');
        }
        $gpg = gnupg_init();
        $key = gnupg_import($gpg, $publicKey);
        gnupg_addencryptkey($gpg, $key['fingerprint']);
        $archiveContent = file_get_contents($archivePath);
        $encryptedContent = gnupg_encrypt($gpg, $archiveContent);
        file_put_contents($outputPath, $encryptedContent);
    }
}
