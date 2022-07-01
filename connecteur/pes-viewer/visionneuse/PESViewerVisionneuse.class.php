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
        /** @var PESViewer $visionneusePES */
        $visionneusePES = $this->getConnector();
        if ($visionneusePES) {
            $result = $visionneusePES->getURL($filepath);
            ?>
            <iframe title="Contenu du PES ALLER" src="<?php echo $result ?>" height="600" width="100%"></iframe>
            <?php
        } else {
            ?>Non disponible<?php
        }



        \exit_wrapper();
    }
}
