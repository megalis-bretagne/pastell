<?php

class RoleDroit
{

    private $documentTypeFactory;

    public function __construct(DocumentTypeFactory $documentTypeFactory)
    {
        $this->documentTypeFactory = $documentTypeFactory;
    }

    public function getAllDroit()
    {
        $droit = array( 'entite:edition',
                        'entite:lecture',
                        'utilisateur:lecture',
                        'utilisateur:edition',
                        'role:lecture',
                        'role:edition',
                        'journal:lecture',
                        'system:lecture',
                        'system:edition',
                        'annuaire:lecture',
                        'annuaire:edition',
                    );
        sort($droit);
        $droit = array_merge($droit, $this->documentTypeFactory->getAllDroit());
        return $droit;
    }
}
