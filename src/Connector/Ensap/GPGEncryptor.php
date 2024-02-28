<?php

namespace Pastell\Connector\Ensap;

use Exception;

class GPGEncryptor
{
    public function encryptArchive($archivePath, $outputPath, $publicKey): void
    {
        // Check if the gnupg extension is loaded
        if (!\extension_loaded('gnupg')) {
            throw new \RuntimeException('The gnupg extension is not loaded.');
        }

        // Check if the archive file exists
        if (!file_exists($archivePath)) {
            throw new \RuntimeException('The archive file does not exist.');
        }

        // Create a new gnupg instance
        $gpg = gnupg_init();

        // Import the public key
        $key = gnupg_import($gpg, $publicKey);

        // Add the public key to the list of keys to encrypt with
        gnupg_addencryptkey($gpg, $key['fingerprint']);

        // Read the archive content
        $archiveContent = file_get_contents($archivePath);

        // Encrypt the archive content
        $encryptedContent = gnupg_encrypt($gpg, $archiveContent);

        // Write the encrypted content to the output file
        file_put_contents($outputPath, $encryptedContent);
    }
}
