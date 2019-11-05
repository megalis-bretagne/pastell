<?php

require_once __DIR__ . "/PESV2XMLFile.class.php";

class PESAllerFile extends PESV2XMLFile
{

    const ID_COLL = 'IdColl';
    const DTE_STR = 'DteStr';
    const COD_BUD = 'CodBud';
    const EXERCICE = 'Exercice';
    const ID_BORD = 'IdBord';
    const ID_PJ = 'IdPJ';
    const ID_PCE = 'IdPce';
    const NOM_FIC = 'NomFic';
    const ID_NATURE = 'IdNature';
    const ID_FONCTION = 'IdFonction';
    const LIBELLE_COD_BUD = 'LibelleColBud';

    /**
     * @param $pes_aller_path
     * @return mixed
     * @throws Exception
     */
    public function getAllInfo($pes_aller_path)
    {
        $xml = $this->getSimpleXMLFromFile($pes_aller_path);

        $info[self::ID_COLL] = $this->getValueFromXPath($xml, "//EnTetePES/IdColl/@V");
        $info[self::DTE_STR] = $this->getValueFromXPath($xml, "//EnTetePES/DteStr/@V");
        $info[self::COD_BUD] = $this->getValueFromXPath($xml, "//EnTetePES/CodBud/@V");

        $info[self::LIBELLE_COD_BUD] = $this->getValueFromXPath($xml, "//EnTetePES/LibelleColBud/@V");


        $info[self::EXERCICE] = $this->getValueFromXPath($xml, "//Bordereau/BlocBordereau/Exer/@V|//PES_PJ/PJ/RefCompta/Exercice/@V");
        $info[self::ID_BORD] = $this->getValueFromXPath($xml, "//Bordereau/BlocBordereau/IdBord/@V");
        $info[self::ID_PJ] = $this->getValueFromXPath($xml, "//PES_PJ/PJ/IdUnique/@V");
        $info[self::ID_PCE] = $this->getValueFromXPath($xml, "//Bordereau/Piece/BlocPiece/InfoPce/IdPce/@V");
        $info[self::NOM_FIC] = $this->getValueFromXPath($xml, "//Enveloppe/Parametres/NomFic/@V");

        $info[self::ID_NATURE] = $this->getValueFromXPath($xml, "//Bordereau/Piece/LigneDePiece/BlocLignePiece/InfoLignePce/Nature/@V");
        $info[self::ID_FONCTION] = $this->getValueFromXPath($xml, "//Bordereau/Piece/LigneDePiece/BlocLignePiece/InfoLignePce/Fonction/@V");
        // PesDepense => InfoLignePce et PesRecette => InfoLignePiece
        if (! $info[self::ID_NATURE]) {
            $info[self::ID_NATURE] = $this->getValueFromXPath($xml, "//Bordereau/Piece/LigneDePiece/BlocLignePiece/InfoLignePiece/Nature/@V");
        }
        if (! $info[self::ID_FONCTION]) {
            $info[self::ID_FONCTION] = $this->getValueFromXPath($xml, "//Bordereau/Piece/LigneDePiece/BlocLignePiece/InfoLignePiece/Fonction/@V");
        }

        return $info;
    }
}
