<?php

class TypeDossierFormulaireElementManager {

    //Bon, c'est pas là que ca devrait être défini, mais c'est forcé par la taille du champs indexable
    const ELEMENT_ID_MAX_LENGTH = 64;

    //Même remarque
    const ELEMENT_ID_REGEXP = "^[0-9a-z_]+$";

    const ELEMENT_ID = "element_id";
    const NAME = "name";
    const TYPE = "type";
    const COMMENTAIRE = "commentaire";
    const REQUIS = "requis";
    const CHAMPS_AFFICHES = "champs_affiches";
    const CHAMPS_RECHERCHE_AVANCEE = "champs_recherche_avancee";
    const TITRE = "titre";

    const TYPE_TEXT = "text";
    const TYPE_FILE = "file";
    const TYPE_MULTI_FILE = "multi_file";
    const TYPE_TEXTAREA = "textarea";
    const TYPE_PASSWORD = "password";
    const TYPE_CHECKBOX = "checkbox";
    const TYPE_DATE = "date";

    public static function getElementPropertiesId(){
        return [
            self::ELEMENT_ID,
            self::NAME,
            self::TYPE,
            self::COMMENTAIRE,
            self::REQUIS,
            self::CHAMPS_AFFICHES,
            self::CHAMPS_RECHERCHE_AVANCEE,
            self::TITRE
        ];
    }

    public static function getAllTypeElement(){
        return [
            self::TYPE_TEXT=>'Texte (une ligne)',
            self::TYPE_FILE=>'Fichier',
            self::TYPE_MULTI_FILE=>'Fichier(s) multiple(s)',
            self::TYPE_TEXTAREA=>'Zone de texte (multi-ligne)',
            self::TYPE_PASSWORD=>'Mot de passe',
            self::TYPE_CHECKBOX=>'Case à cocher',
            self::TYPE_DATE=>'Date'
        ];
    }

    public static function getTypeElementLibelle($id){
        $all_type = self::getAllTypeElement();
        if (! isset($all_type[$id])){
            return false;
        }
        return self::getAllTypeElement()[$id];
    }

    public function getElementFromArray(array $properties){
        $newFormElement = new TypeDossierFormulaireElement();

        foreach(self::getElementPropertiesId() as $key){
            if (isset($properties[$key])) {
                $newFormElement->$key = $properties[$key];
            } else {
                $newFormElement->$key = false;
            }
        }
        return $newFormElement;
    }

    /**
     * @param TypeDossierFormulaireElement $typeDossierFormulaireElement
     * @param Recuperateur $recuperateur
     * @return bool
     * @throws TypeDossierException
     */
    public function edition(
        TypeDossierFormulaireElement $typeDossierFormulaireElement,
        Recuperateur $recuperateur
    ){
        $this->verifElementId($recuperateur->get(self::ELEMENT_ID));
        $this->verifType($recuperateur->get(self::TYPE));
        foreach (self::getElementPropertiesId() as $element_formulaire){
            $typeDossierFormulaireElement->$element_formulaire = $recuperateur->get($element_formulaire);
        }
        return true;
    }

    /**
     * @param $element_id
     * @throws TypeDossierException
     */
    private function verifElementId($element_id){
        if(! $element_id){
            throw new TypeDossierException("L'identifiant ne peut être vide");
        }
        if (strlen($element_id) > self::ELEMENT_ID_MAX_LENGTH){
            throw new TypeDossierException("La longueur de l'identifiant ne peut dépasser 64 caractères");
        }
        if (! preg_match("#".self::ELEMENT_ID_REGEXP."#",$element_id)){
            throw new TypeDossierException(
                "L'identifiant de l'élément ne peut comporter que des chiffres, des lettres minuscules et le caractère _"
            );
        }
    }

    /**
     * @param $type
     * @throws TypeDossierException
     */
    private function verifType($type){
        if( ! self::getTypeElementLibelle($type)){
            throw new TypeDossierException(
              "Le type n'existe pas"
            );
        }
    }

}