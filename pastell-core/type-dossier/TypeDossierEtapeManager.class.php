<?php

class TypeDossierEtapeManager {

    const NUM_ETAPE = "num_etape";
    const TYPE = "type";
    const REQUIS = "requis";
    const AUTOMATIQUE = "automatique";
    const SPECIFIC_TYPE_INFO = "specific_type_info";

    public static function getPropertiesId(){
        return [
            self::NUM_ETAPE,
            self::TYPE,
            self::REQUIS,
            self::AUTOMATIQUE
        ];
    }

    public function getEtapeFromArray(array $etape_info,$fomulaire_configuration){
        $newFormEtape = new TypeDossierEtape();
        foreach(TypeDossierEtapeManager::getPropertiesId() as $key ){
            if (isset($etape_info[$key])) {
                $newFormEtape->$key = $etape_info[$key];
            }
        }
        foreach($fomulaire_configuration as $element_id => $element_info){
            if (isset($etape_info['specific_type_info'][$element_id])) {
                $newFormEtape->specific_type_info[$element_id] = $etape_info['specific_type_info'][$element_id];
            } else {
                $newFormEtape->specific_type_info[$element_id] = "";
            }
        }
        return $newFormEtape;
    }

}