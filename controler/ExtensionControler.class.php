<?php

class ExtensionControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $this->verifDroit(0, "system:lecture");
        $this->setViewParameter('menu_gauche_template', "ConfigurationMenuGauche");
        $this->setViewParameter('menu_gauche_select', "Extension/index");
        $this->setViewParameter('dont_display_breacrumbs', true);
    }

    public function indexAction()
    {
        $this->verifDroit(0, "system:lecture");
        $this->setViewParameter('droitEdition', $this->hasDroit(0, "system:edition"));
        $this->setViewParameter('all_extensions', $this->extensionList());

        $this->setViewParameter('pastell_manifest', $this->getManifestFactory()->getPastellManifest()->getInfo());
        $this->setViewParameter('extensions_graphe', $this->getObjectInstancier()
            ->getInstance(ExtensionsGraphique::class)
            ->creerGraphe());

        $this->setViewParameter('template_milieu', "ExtensionIndex");
        $this->setViewParameter('page_title', "Extensions");
        if ($this->hasDroit(0, "system:edition")) {
            $this->setViewParameter('nouveau_bouton_url', array("Ajouter" => "Extension/edition"));
        }
        $this->renderDefault();
    }

    public function extensionList()
    {
        $result = $this->apiGet("/extension");
        return $result['result'];
    }

    public function detailAction()
    {
        $id_e = $this->getGetInfo()->get("id_extension");
        $extension_info = $this->getExtensions()->getInfo($id_e);

        $this->setViewParameter('extension_info', $extension_info);
        $this->setViewParameter('template_milieu', "ExtensionDetail");
        $this->setViewParameter('page_title', "Extension « {$extension_info['nom']} »");

        $this->renderDefault();
    }

    public function changelogAction()
    {
        $id_e = $this->getGetInfo()->get("id_extension");
        $extension_info = $this->getExtensions()->getInfo($id_e);
        $this->setViewParameter('page_title', "Journal des modifications (CHANGELOG) de l'extension « {$extension_info['nom']} » ");
        $this->setViewParameter('template_milieu', "SystemChangelog");

        $changelog_file_path  = $extension_info['path'] . "/CHANGELOG.md";
        if (!file_exists($changelog_file_path)) {
            $this->setViewParameter('changelog', "Le CHANGELOG n'est pas disponible pour cette extension");
        } else {
            $text = file_get_contents($changelog_file_path);
            $parsedown = new Parsedown();
            $text = $parsedown->parse($text);
            $text = preg_replace("/<h2>/", "<h3>", $text);
            $this->setViewParameter('changelog', preg_replace("/<h1>/", "<h2>", $text));
        }

        $this->renderDefault();
    }

    public function editionAction()
    {
        $this->verifDroit(0, "system:edition");
        $id_e = $this->getGetInfo()->get("id_extension", 0);
        $extension_info = $this->getExtensionSQL()->getInfo($id_e);
        if (!$extension_info) {
            $extension_info = array('id_e' => 0,'path' => '');
        }
        $this->setViewParameter('extension_info', $extension_info);
        $this->setViewParameter('template_milieu', "ExtensionEdition");
        if ($id_e) {
            $this->setViewParameter('page_title', "Modification de l'emplactement d'une extension");
        } else {
            $this->setViewParameter('page_title', "Ajout d'une extension");
        }

        $this->renderDefault();
    }

    public function doEditionAction()
    {
        try {
            $id_extension = $this->getPostInfo()->getInt('id_extension');
            if ($id_extension) {
                $this->apiPatch("/extension/$id_extension");
            } else {
                $this->apiPost("/extension");
            }

            $this->setLastMessage("Extension éditée");
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
        }

        $this->redirect("/Extension/index");
    }

    public function deleteAction()
    {
        try {
            $id_extension = $this->getPostInfo()->getInt('id_extension');
            $this->apiDelete("/extension/{$id_extension}");
            $this->setLastMessage("Extension supprimée");
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
        }
        $this->redirect("/Extension/index");
    }

    public function graphiqueAction()
    {
        if (! file_exists($this->getObjectInstancier()->getInstance(ExtensionsGraphique::class)->getGraphiquePath())) {
            $file = __DIR__ . "/../web/img/commun/logo_pastell.png";
            header("Content-type: image/png");
            readfile($file);
        } else {
            header("Content-type: image/jpeg");
            readfile($this->getObjectInstancier()->getInstance(ExtensionsGraphique::class)->getGraphiquePath());
        }
    }
}
