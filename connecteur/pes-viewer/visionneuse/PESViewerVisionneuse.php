<?php

declare(strict_types=1);

use Pastell\Viewer\Viewer;

class PESViewerVisionneuse implements Viewer
{
    private ConnecteurFactory $connecteurFactory;

    public function __construct(ConnecteurFactory $connecteurFactory)
    {
        $this->connecteurFactory = $connecteurFactory;
    }

    /**
     * @throws UnrecoverableException
     */
    public function display(string $filename, string $filepath): void
    {
        /** @var PESViewer $visionneusePES */
        $visionneusePES = $this->connecteurFactory->getGlobalConnecteur(PESViewer::CONNECTEUR_TYPE_ID);

        if ($visionneusePES) {
            $result = $visionneusePES->getURL($filepath);
            echo '<iframe title="Contenu du PES ALLER" src="' . $result . '" height="600" width="100%"></iframe>';
        } else {
            echo 'Non disponible';
        }
    }
}
