<?php

class RoleDroit
{

    private $documentTypeFactory;
    private $connecteur_droit;

    public function __construct(DocumentTypeFactory $documentTypeFactory, bool $connecteur_droit = false)
    {
        $this->documentTypeFactory = $documentTypeFactory;
        $this->connecteur_droit = $connecteur_droit;
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
        if ($this->connecteur_droit) {
            $droit[] = 'connecteur:lecture';
            $droit[] = 'connecteur:edition';
        }
        sort($droit);
        return array_merge($droit, $this->documentTypeFactory->getAllDroit());
    }

    /**
     * @param array $all_droit
     * @return array
     */
    public function cleanDisabledDroit(array $all_droit): array
    {
        foreach ($all_droit as $sql_droit => $checked) {
            list($part) = explode(":", $sql_droit);
            if (! $this->documentTypeFactory->isEnabledFlux($part)) {
                unset($all_droit[$sql_droit]);
            }
        }
        return $all_droit;
    }
}
