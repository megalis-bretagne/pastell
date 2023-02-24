<?php

use Pastell\Service\Pack\PackService;

class AideControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $this->setViewParameter('pages_without_left_menu', true);
        $this->setViewParameter('dont_display_breacrumbs', true);
    }

    /**
     * @throws NotFoundException
     */
    public function RGPDAction()
    {
        $this->setViewParameter('page_title', "RGPD");
        $this->setViewParameter('template_milieu', "AideRGPD");
        $file = $this->getObjectInstancier()->getInstance('rgpd_page_path');
        $this->setViewParameter('rgpd_content', $this->parsedown($file));
        $this->renderDefault();
    }

    private function parsedown($file_path)
    {
        if (! file_exists($file_path) || ! is_readable($file_path)) {
            return "<div class='alert alert-danger'>Le contenu du fichier $file_path ne peut être lu</div>";
        }
        $text = file_get_contents($file_path);
        $parsedown = new Parsedown();
        $text = $parsedown->parse($text);

        $text = preg_replace("/<h2>/", "<h3>", $text);
        $text = preg_replace("/<\/h2>/", "</h3>", $text);
        $text = preg_replace("/<h1>/", "<h2>", $text);
        $text = preg_replace("/<\/h1>/", "</h2>", $text);
        return $text;
    }

    /**
     * @throws NotFoundException
     */
    public function AProposAction()
    {
        $this->setViewParameter('page_title', "À propos");
        $this->setViewParameter('template_milieu', "AideAPropos");
        $this->setViewParameter('changelog', $this->parsedown(__DIR__ . "/../CHANGELOG.md"));
        $this->setViewParameter('manifest_info', $this->getManifestFactory()->getPastellManifest()->getInfo());

        /** @var PackService $packService */
        $packService = $this->getInstance(PackService::class);
        $this->setViewParameter('listPack', $packService->getListPack());

        $this->renderDefault();
    }
}
