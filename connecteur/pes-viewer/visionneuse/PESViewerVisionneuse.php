<?php

declare(strict_types=1);

use Pastell\Viewer\ConnectorViewer;

class PESViewerVisionneuse extends ConnectorViewer
{
    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function display(string $filename, string $filepath): void
    {
        $visionneusePES = $this->getConnector();
        if ($visionneusePES) {
            /** @var PESViewer $visionneusePES */
            $result = $visionneusePES->getURL($filepath);
            echo '<iframe title="Contenu du PES ALLER" src="' . $result . '" height="600" width="100%"></iframe>';
        } else {
            echo 'Non disponible';
        }
    }
}
