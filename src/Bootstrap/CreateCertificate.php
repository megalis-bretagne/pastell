<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

use Exception;
use PastellLogger;
use Symfony\Component\Process\Process;

class CreateCertificate implements InstallableBootstrap
{
    private const GENERATE_KEY_PAIR_SCRIPT = __DIR__ . "/../../docker/generate-key-pair.sh";

    public function __construct(
        private readonly string $certificate_path,
        private readonly string $site_base,
        private readonly PastellLogger $pastellLogger,
    ) {
    }

    public function getName(): string
    {
        return 'Certificat HTTPS';
    }

    private function getHostname(): string
    {
        return parse_url(
            $this->site_base,
            PHP_URL_HOST
        );
    }

    /**
     * @throws Exception
     */
    public function install(): InstallResult
    {
        if (file_exists($this->getPath("privkey.pem"))) {
            return InstallResult::NothingToDo;
        }
        $hostname = $this->getHostname();

        $letsencrypt_cert_path = "/etc/letsencrypt/live/$hostname";
        $privkey_path  = "$letsencrypt_cert_path/privkey.pem";
        $cert_path  = "$letsencrypt_cert_path/fullchain.pem";
        if (file_exists($privkey_path)) {
            $this->pastellLogger->info("Certificat letsencrypt trouvé !");
            symlink($privkey_path, $this->getPath("privkey.pem"));
            symlink($cert_path, $this->getPath("fullchain.pem"));
            return InstallResult::InstallOk;
        }

        $process = new Process([self::GENERATE_KEY_PAIR_SCRIPT, $hostname, $this->certificate_path]);
        $process->run();

        $this->pastellLogger->info($process->getOutput());

        return $process->isSuccessful() ? InstallResult::InstallOk: InstallResult::InstallFailed;
    }

    private function getPath(string $file_or_directory): string
    {
        return sprintf("%s/%s", $this->certificate_path, $file_or_directory);
    }
}
