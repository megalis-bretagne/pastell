<?php

class PESViewerVisionneuse extends Visionneuse
{
    private $connecteurFactory;

    public function __construct(ConnecteurFactory $connecteurFactory)
    {
        $this->connecteurFactory = $connecteurFactory;
    }

    /**
     * @param string $filename
     * @param string $filepath
     * @throws Exception
     */
    public function display($filename, $filepath)
    {
        /** @var PESViewer $visionneusePES */
        $visionneusePES = ($this->connecteurFactory->getGlobalConnecteur('visionneuse_pes'));
        if ($visionneusePES) {
            $result = $visionneusePES->getURL($filepath);
            ?>
            <iframe title="Contenu du PES ALLER" src="<?php echo $result ?>" height="600" width="100%"></iframe>
            <?php
        } else {
            ?>Non disponible<?php
        }



        exit_wrapper();
    }
}