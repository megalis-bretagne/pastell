<?php

class PastellUpdater
{

    /**
     * @var PastellLogger
     */
    private $pastellLogger;

    /**
     * @var ObjectInstancier
     */
    private $objectInstancier;

    public function __construct(PastellLogger $pastellLogger, ObjectInstancier $objectInstancier)
    {
        $this->pastellLogger = $pastellLogger;
        $this->objectInstancier = $objectInstancier;
    }

    public function update()
    {
        $this->to301();
        $this->to302();
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

    public function to302()
    {
        $this->pastellLogger->info('Start script to 3.0.2');

        $connecteurEntiteSql = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class);
        $fastParapheurConnectors = $connecteurEntiteSql->getAllById('fast-parapheur');
        foreach ($fastParapheurConnectors as $fastParapheurConnector) {
            if ($fastParapheurConnector['id_e'] === '0') {
                continue;
            }
            $id_ce = $fastParapheurConnector['id_ce'];
            $this->objectInstancier->getInstance(ConnecteurFactory::class)->getConnecteurById($id_ce);
            $connecteurConfig = $this->objectInstancier->getInstance(ConnecteurFactory::class)
                ->getConnecteurConfig($id_ce);
            $oldUrl = $connecteurConfig->get('wsdl');
            $newUrl = str_replace(FastParapheur::WSDL_URI, '', $oldUrl);

            $connecteurConfig->setData('wsdl', $newUrl);
            $this->pastellLogger->info('id_ce => ' . $id_ce);
            $this->pastellLogger->info('old URL => ' . $oldUrl);
            $this->pastellLogger->info('new URL => ' . $newUrl);
        }
        $this->pastellLogger->info('End script to 3.0.2');
    }
}
