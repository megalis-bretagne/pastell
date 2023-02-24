<?php

/**
 * Class FluxDataSedaPDFGenerique
 * @deprecated PA 3.0 les profils doivent être mis à jour pour :
 * 1) ne pas utiliser deux fois le sha256 d'un fichier
 * 2) utiliser le mot clé 'size' introduit dans PA 3.0
 */
class FluxDataSedaPDFGenerique extends FluxDataSedaDefault
{
    public function get_is_recupere($key)
    {
        return $this->donneesFormulaire->get($key) ? 'MAIL_RECUPERE_OUI' : 'MAIL_RECUPERE_NON';
    }

    /** On utilise deux fois la fonction sha256 sur document, du coup, ca marche plus... */
    public function getFileSHA256($key)
    {
        if ($key == 'document') {
            return hash_file("sha256", $this->donneesFormulaire->getFilePath('document'));
        }
        return parent::getFileSHA256($key);
    }

    //Les anciens profils n'utilisent pas le mot-clé size
    /**
     * @return false|int
     * @throws DonneesFormulaireException
     */
    public function get_journal_size_in_bytes()
    {
        return parent::getFilesize('journal');
    }

    /**
     * @return false|int
     * @throws DonneesFormulaireException
     */
    public function get_document_size_in_bytes()
    {
        return parent::getFilesize('document');
    }

    /**
     * @return false|int
     * @throws DonneesFormulaireException
     */
    public function get_annexe_size_in_bytes()
    {
        return parent::getFilesize('annexe');
    }
}
