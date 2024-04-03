<?php

declare(strict_types=1);

use Pastell\Viewer\Viewer;

class PESViewerVisionneuse implements Viewer
{
    public function __construct(
        private readonly ConnecteurFactory $connecteurFactory
    ) {
    }

    /**
     * @throws UnrecoverableException
     */
    public function display(string $filename, string $filepath): void
    {
        /** @var PESViewer|false $visionneusePES */
        $visionneusePES = $this->connecteurFactory->getGlobalConnecteur(PESViewer::CONNECTEUR_TYPE_ID);

        if ($visionneusePES !== false) {
            $result = $visionneusePES->getURL($filepath);
            echo '<iframe title="Contenu du PES ALLER" src="' . $result . '" height="600" width="100%"></iframe>';
        } else {
            echo 'Non disponible';
        }
    }
}
