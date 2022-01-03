<?php

class FluxAPIController extends BaseAPIController
{
    /** @var  DocumentTypeFactory */
    private $documentTypeFactory;

    public function __construct(DocumentTypeFactory $documentTypeFactory)
    {
        $this->documentTypeFactory = $documentTypeFactory;
    }

    public function get()
    {
        $id_flux = $this->getFromQueryArgs(0);
        $action = $this->getFromQueryArgs(1);
        if (! $id_flux) {
            return $this->listFlux();
        }

        if (! $this->documentTypeFactory->isTypePresent($id_flux)) {
            throw new NotFoundException("Le flux $id_flux n'existe pas sur cette plateforme");
        }
        $this->checkOneDroit("$id_flux:lecture");

        if ($action == "action") {
            return $this->listAction($id_flux);
        }

        return $this->getFlux($id_flux);
    }

    public function listFlux()
    {
        $allDocType = $this->documentTypeFactory->getAllType();
        $allType = array();
        foreach ($allDocType as $type_flux => $les_flux) {
            foreach ($les_flux as $nom => $affichage) {
                if ($this->hasOneDroit($nom . ":lecture")) {
                    $allType[$nom]  = array('type' => $type_flux,'nom' => $affichage);
                }
            }
        }
        return $allType;
    }

    public function getFlux($id_flux)
    {
        $documentType = $this->documentTypeFactory->getFluxDocumentType($id_flux);
        $formulaire = $documentType->getFormulaire();
        $result = array();
        /**
         * @var Field $fields
         */
        foreach ($formulaire->getAllFields() as $key => $fields) {
            $result[$key] = $fields->getAllProperties();
        }
        return $result;
    }

    public function listAction($id_flux)
    {
        $documentType = $this->documentTypeFactory->getFluxDocumentType($id_flux);
        return $documentType->getTabAction();
    }
}
