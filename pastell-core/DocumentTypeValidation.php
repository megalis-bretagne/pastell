<?php

/** @deprecated Since 4.0.4, Use Pastell\Configuration\DocumentTypeValidation instead */

class DocumentTypeValidation
{
    public const MODULE_DEFINITION = 'module-definition.yml';

    private $module_definition;
    private $last_error;

    private $list_pack = [];
    private $connecteur_type_list = [];
    private $entite_type_list = [];

    public function __construct(
        private readonly YMLLoader $yml_loader,
        private readonly string $data_dir,
    ) {
        $this->module_definition = $yml_loader->getArray($this->data_dir . '/' . self::MODULE_DEFINITION);
    }

    public function getLastError()
    {
        return $this->last_error;
    }

    public function getModuleDefinition()
    {
        $module_def = $this->module_definition;
        foreach ($module_def as $part => $properties) {
            if (! isset($properties['info'])) {
                $module_def[$part]['info'] = "";
            }
            foreach ($properties['possible_key'] as $key => $key_properties) {
                if (! isset($key_properties['info'])) {
                    $module_def[$part]['possible_key'][$key]['info'] = "";
                }
            }
        }
        return $module_def;
    }

    public function setListPack(array $list_pack)
    {
        $this->list_pack = $list_pack;
    }

    public function setConnecteurTypeList(array $connecteur_type_list)
    {
        $this->connecteur_type_list = $connecteur_type_list;
    }

    public function setEntiteTypeList(array $entite_type_list)
    {
        $this->entite_type_list = $entite_type_list;
    }

    public function validate($definition_file_path)
    {
        $this->last_error = [];

        $typeDefinition = $this->yml_loader->getArray($definition_file_path);
        if (! is_array($typeDefinition)) {
            $this->last_error[] = "Fichier definition.yml absent";
            return false;
        }
        $result = $this->validatePart('definition.yml', $typeDefinition, '');
        $result &= $this->validatePageCondition($typeDefinition);
        $result &= $this->validateOneTitre($typeDefinition);
        $result &= $this->validateChoiceAction($typeDefinition);
        $result &= $this->validateRestrictionPack($typeDefinition, $this->list_pack);
        $result &= $this->validateConnecteur($typeDefinition, $this->connecteur_type_list);
        $result &= $this->validateOnChange($typeDefinition);
        $result &= $this->validateIsEqual($typeDefinition);
        $result &= $this->validateReadOnlyContent($typeDefinition);
        $result &= $this->validateRuleAction($typeDefinition, 'last-action');
        $result &= $this->validateRuleAction($typeDefinition, 'no-action');
        $result &= $this->validateRuleAction($typeDefinition, 'has-action');
        $result &= $this->validateActionProperties($typeDefinition, 'action-automatique');
        $result &= $this->validateActionProperties($typeDefinition, 'accuse_de_reception_action');
        $result &= $this->validateEditableContent($typeDefinition);
        $result &= $this->validateDepend($typeDefinition);
        $result &= $this->validateRuleContent($typeDefinition);
        $result &= $this->validateActionSelection($typeDefinition, $this->entite_type_list);
        $result &= $this->validateRuleTypeIdE($typeDefinition, $this->entite_type_list);
        $result &= $this->validateActionClass($typeDefinition);
        $result &= $this->validateChampsAffiche($typeDefinition);
        $result &= $this->validateChampsRechercheAvancee($typeDefinition);
        $result &= $this->validateActionConnecteurType($typeDefinition);
        return $result ? true : false;
    }

    private function validateChampsRechercheAvancee($typeDefinition)
    {
        $result = true;
        $all_champs_affiche = $this->getList($typeDefinition, 'champs-recherche-avancee');
        $all_element_name = $this->getAllElementIndexed($typeDefinition);
        foreach ($all_champs_affiche as $champs) {
            if (in_array($champs, ['type','id_e','lastetat','last_state_begin','etatTransit','state_begin','notEtatTransit','search','tri'])) {
                continue;
            }
            if (in_array($champs, $all_element_name)) {
                continue;
            }
            $this->last_error[] = "champs-affiches:<b>$champs</b> n'est pas une valeur par défaut ou un élement indexé du formulaire";
            $result = false;
        }
        return $result;
    }

    private function validateChampsAffiche($typeDefinition)
    {
        $result = true;
        $all_champs_affiche = $this->getList($typeDefinition, 'champs-affiches');
        $all_element_name = $this->getAllElementIndexed($typeDefinition);
        foreach ($all_champs_affiche as $champs) {
            if (in_array($champs, ['titre','entite','dernier_etat','date_dernier_etat'])) {
                continue;
            }
            if (in_array($champs, $all_element_name)) {
                continue;
            }
            $this->last_error[] = "champs-affiches:<b>$champs</b> n'est pas une valeur par défaut ou un élement indexé du formulaire";
            $result = false;
        }
        return $result;
    }

    private function validateActionClass(array $typeDefinition): bool
    {
        $actions = $this->getList($typeDefinition, 'action');
        $result = true;
        foreach ($actions as $action_name => $action) {
            if (empty($action['action-class'])) {
                continue;
            }
            if (!\class_exists($action['action-class'])) {
                $this->last_error[] = \sprintf(
                    "action:%s:action-class:<b>%s</b> n'est pas disponible sur le système",
                    $action_name,
                    $action['action-class']
                );
                $result = false;
            } elseif (!\is_subclass_of($action['action-class'], ActionExecutor::class)) {
                $this->last_error[] = \sprintf(
                    "action:%s:action-class:<b>%s</b> n'étends pas %s",
                    $action_name,
                    $action['action-class'],
                    ActionExecutor::class,
                );
                $result = false;
            }
        }
        return $result;
    }

    private function validateRuleTypeIdE($typeDefinition, $all_type_entite)
    {
        $all_type = $this->getElementRuleValue($typeDefinition, 'type_id_e');
        $result = true;
        foreach ($all_type as $type) {
            if (! in_array($type, $all_type_entite)) {
                $this->last_error[] = "action:*:rule:type_id_e:<b>$type</b></b> n'est pas un type d'entité du système";
                $result = false;
            }
        }
        return $result;
    }

    private function validateActionSelection($typeDefinition, $all_type_entite)
    {

        $all_action = $this->getList($typeDefinition, 'action');
        $result = true;
        foreach ($all_action as $action_name => $action) {
            if (empty($action['action-selection'])) {
                continue;
            }
            if (! in_array($action['action-selection'], $all_type_entite)) {
                $this->last_error[] = "action:$action_name:action-selection:<b>{$action['action-selection']}</b> n'est pas un type d'entité du système";
                $result = false;
            }
        }
        return $result;
    }

    private function validateDepend($typeDefinition)
    {
        $result = true;
        $all_element_name = $this->getAllElementName($typeDefinition);
        foreach ($this->getList($typeDefinition, 'formulaire') as $onglet => $element_list) {
            foreach ($element_list as $name => $prop) {
                if (empty($prop['depend'])) {
                    continue;
                }
                if (! in_array($prop['depend'], $all_element_name)) {
                    $this->last_error[] = "<b>formulaire:$onglet:$name:depend:{$prop['depend']}</b> n'est pas un élement du formulaire";
                    $result = false;
                }
            }
        }
        return $result;
    }

    private function validateEditableContent($typeDefinition)
    {
        $all_element_name = $this->getAllElementName($typeDefinition);
        $editable_content_list = [];
        $all_action = $this->getList($typeDefinition, 'action');
        foreach ($all_action as $action) {
            if (empty($action['editable-content'])) {
                continue;
            }
            $editable_content_list = array_merge($editable_content_list, $action['editable-content']);
        }

        $result = true;
        foreach ($editable_content_list as $editable_content) {
            if (! in_array($editable_content, $all_element_name)) {
                $this->last_error[] = "formulaire:xx:yy:editable-content:<b>$editable_content</b> n'est pas défini dans le formulaire";
                $result = false;
            }
        }
        return $result;
    }

    private function validateIsEqual($typeDefinition)
    {
        $all_element_name = $this->getAllElementName($typeDefinition);
        $all_is_equal = $this->getElementPropertiesValue($typeDefinition, 'is_equal');
        $result = true;
        foreach ($all_is_equal as $is_equal) {
            if (! in_array($is_equal, $all_element_name)) {
                $this->last_error[] = "formulaire:xx:yy:is_equal:<b>$is_equal</b> n'est pas défini dans le formulaire";
                $result = false;
            }
        }
        return $result;
    }

    private function validateRuleContent($typeDefinition)
    {
        $all_content = $this->getElementRuleValue($typeDefinition, 'content');
        $all_element_name = $this->getAllElementName($typeDefinition);
        $result = true;
        foreach ($all_content as $key => $content) {
            if (! in_array($key, $all_element_name)) {
                $this->last_error[] = "action:xx:rule:content:<b>$key</b> n'est pas défini dans le formulaire";
                $result = false;
            }
        }
        return $result;
    }


    private function validateReadOnlyContent($typeDefinition)
    {
        $all_element_name = $this->getAllElementName($typeDefinition);
        $all_is_equal = $this->getElementPropertiesValue($typeDefinition, 'read-only-content');
        $result = true;
        foreach ($all_is_equal as $is_equal) {
            foreach ($is_equal as $name => $prop) {
                if (! in_array($name, $all_element_name)) {
                    $this->last_error[] = "formulaire:xx:yy:read-only-content:<b>$name</b> n'est pas défini dans le formulaire";
                    $result = false;
                }
            }
        }
        return $result;
    }


    private function getAllElementName($typeDefinition)
    {
        $result = [];
        foreach ($this->getList($typeDefinition, 'formulaire') as $onglet => $element_list) {
            if (! $element_list) {
                $this->last_error[] = "formulaire:onglet: est vide";
                continue;
            }
            foreach ($element_list as $name => $prop) {
                $result[] = $name;
                $result[] = Field::Canonicalize($name);
            }
        }
        return $result;
    }

    private function getAllElementIndexed($typeDefinition)
    {
        $result = [];
        foreach ($this->getList($typeDefinition, 'formulaire') as $onglet => $element_list) {
            foreach ($element_list as $name => $prop) {
                if (empty($prop['index'])) {
                    continue;
                }
                $result[] = $name;
                $result[] = Field::Canonicalize($name);
            }
        }
        return $result;
    }

    private function validateRestrictionPack(array $typeDefinition, array $list_pack): bool
    {
        $restriction_pack_list = $this->getList($typeDefinition, 'restriction_pack');
        $result = true;
        foreach ($restriction_pack_list as $restriction_pack) {
            if (!array_key_exists($restriction_pack, $list_pack)) {
                $this->last_error[] = "restriction_pack :<b>$restriction_pack</b> n'est pas défini dans la liste des suppléments";
                $result = false;
            }
        }
        return $result;
    }

    private function validateConnecteur(array $typeDefinition, array $connecteur_type_list)
    {
        $connecteur_list = $this->getList($typeDefinition, 'connecteur');
        $result = true;
        foreach ($connecteur_list as $connecteur) {
            if (!in_array($connecteur, $connecteur_type_list)) {
                $this->last_error[] = "connecteur:<b>$connecteur</b> n'est défini dans aucun connecteur du système";
                $result = false;
            }
        }

        return $result;
    }

    private function validateActionProperties(array $typeDefinition, $properties)
    {
        $action_list = $this->getActionPropertiesValue($typeDefinition, $properties);
        return $this->checkIsAction($typeDefinition, $action_list);
    }

    private function getKeys(array $definition, $key_name)
    {
        if (empty($definition[$key_name])) {
            return [];
        }
        return array_keys($definition[$key_name]);
    }

    private function getList(array $definition, $key_name)
    {
        if (empty($definition[$key_name])) {
            return [];
        }
        return $definition[$key_name];
    }

    private function validateChoiceAction($typeDefinition)
    {
        $choice_action_list = $this->getElementPropertiesValue($typeDefinition, 'choice-action');
        return $this->checkIsAction($typeDefinition, $choice_action_list);
    }

    private function validateOnChange($typeDefinition)
    {
        $all_action = $this->getElementPropertiesValue($typeDefinition, 'onchange');
        return $this->checkIsAction($typeDefinition, $all_action);
    }

    private function validateRuleAction($typeDefinition, $rule_name)
    {
        $all_action = $this->getElementRuleValue($typeDefinition, $rule_name);
        return $this->checkIsAction($typeDefinition, $all_action);
    }

    private function validateActionConnecteurType($typeDefinition)
    {
        $result = true;

        if (empty($typeDefinition['action'])) {
            return $result;
        }
        $element_name_list = $this->getAllElementName($typeDefinition);
        $all_action = $this->getKeys($typeDefinition, 'action');

        foreach ($typeDefinition['action'] as $action_name => $action_properties) {
            if (empty($action_properties['connecteur-type'])) {
                continue;
            }
            if (!in_array($action_properties['connecteur-type'], $this->connecteur_type_list)) {
                $this->last_error[] = "action:<b>{$action_name}</b>:connecteur-type:" .
                    "<b>{$action_properties['connecteur-type']}</b> n'est pas un connecteur du système";
                $result = false;
            }
            if (empty($action_properties['connecteur-type-action'])) {
                continue;
            }
            if (
                !is_subclass_of($action_properties['connecteur-type-action'], ActionExecutor::class)
            ) {
                $this->last_error[] = "action:<b>{$action_name}</b>:connecteur-type-action:" .
                    "<b>{$action_properties['connecteur-type-action']}</b> n'est pas une classe d'action du système";
                $result = false;
            }

            if (empty($action_properties['connecteur-type-mapping'])) {
                continue;
            }

            foreach ($action_properties['connecteur-type-mapping'] as $key => $element_name) {
                if (! in_array($element_name, $element_name_list) && ! in_array($element_name, $all_action)) {
                    $this->last_error[] =  "action:<b>{$action_name}</b>:connecteur-type-mapping:$key:" .
                        "<b>$element_name</b> n'est pas un élément du formulaire";
                    $result = false;
                }
            }
        }
        return $result;
    }


    private function checkIsAction($typeDefinition, $list_verif)
    {
        $all_action = $this->getKeys($typeDefinition, 'action');
        $result = true;
        foreach ($list_verif as $verif_action) {
            if ($verif_action == ActionPossible::FATAL_ERROR_ACTION) {
                continue;
            }
            if (! in_array($verif_action, $all_action)) {
                $this->last_error[] = "formulaire:xx:<b>$verif_action</b> qui n'est pas une clé de <b>action</b>";
                $result = false;
            }
        }
        return $result;
    }

    private function getActionPropertiesValue($typeDefinition, $properties_name)
    {
        if (empty($typeDefinition['action'])) {
            return [];
        }
        $properties_list = [];
        foreach ($typeDefinition['action'] as $action_name => $action_properties) {
            if (empty($action_properties[$properties_name])) {
                continue;
            }
            $properties_list[] = $action_properties[$properties_name];
        }
        return $properties_list;
    }


    private function getElementRuleValue($typeDefinition, $rule_name)
    {
        if (empty($typeDefinition['action'])) {
            return [];
        }
        $properties_list = [];
        foreach ($typeDefinition['action'] as $action_name => $action_properties) {
            if (empty($action_properties['rule'])) {
                continue;
            }
            $prop = $this->findRule($action_properties['rule'], $rule_name);
            if ($prop) {
                $properties_list = array_merge($properties_list, $prop);
            }
        }
        return $properties_list;
    }

    private function findRule(array $rule_array, $rule_name)
    {
        $result = [];
        if (isset($rule_array[$rule_name])) {
            if (! is_array($rule_array[$rule_name])) {
                $result = [$rule_array[$rule_name]];
            } else {
                $result = $rule_array[$rule_name];
            }
        }
        foreach ($rule_array as $r_name => $r_properties) {
            if (mb_substr($r_name, 0, 3) == 'or_' || mb_substr($r_name, 0, 4) == 'and_') {
                $result = array_merge($result, $this->findRule($r_properties, $rule_name));
            }
        }

        return $result;
    }



    private function getElementPropertiesValue($typeDefinition, $properties)
    {
        if (empty($typeDefinition['formulaire'])) {
            return [];
        }
        $properties_list = [];
        foreach ($typeDefinition['formulaire'] as $onglet => $formulaire_properties) {
            foreach ($formulaire_properties as $element_name => $element_properties) {
                if (isset($element_properties[$properties])) {
                    $properties_list[] = $element_properties[$properties];
                }
            }
        }
        return $properties_list;
    }


    private function validatePageCondition($typeDefinition)
    {
        $all_element_name = $this->getAllElementName($typeDefinition);

        $all_page_condition = $this->getKeys($typeDefinition, 'page-condition');
        $all_page = $this->getKeys($typeDefinition, 'formulaire');
        $result = true;
        foreach ($all_page_condition as $page_condition) {
            if (! in_array($page_condition, $all_page)) {
                $this->last_error[] = "page-condition:<b>$page_condition</b> qui n'est pas une clé de <b>formulaire</b>";
                $result = false;
                continue;
            }
            foreach ($typeDefinition['page-condition'][$page_condition] as $element => $test) {
                if (!in_array($element, $all_element_name)) {
                    $this->last_error[] = "page-condition:<b>$page_condition:$element</b> qui n'est pas un élement du <b>formulaire</b>";
                    $result = false;
                }
            }
        }
        return $result;
    }

    private function validateOneTitre($typeDefinition)
    {
        if (empty($typeDefinition['formulaire'])) {
            return true;
        }
        $titre = [];
        foreach ($typeDefinition['formulaire'] as $onglet => $formulaire_properties) {
            foreach ($formulaire_properties as $element_name => $element_properties) {
                if (isset($element_properties['title'])) {
                    $titre[] = $element_name;
                }
            }
        }
        if (count($titre) > 1) {
            $this->last_error[] = "Plusieurs élements trouvé avec la propriété « <b>title</b> » : " . implode(",", $titre);
            return false;
        }
        return true;
    }

    private function validatePart($part, $typeDefinition, $previous_part)
    {
        if (! $typeDefinition) {
            return false;
        }
        if ($previous_part) {
            $new_part = "$previous_part:$part";
        } else {
            $new_part = "$part";
        }
        foreach ($typeDefinition as $key => $data) {
            $key_info = $this->getPossibleKeyInfo($part, $key);
            if (! $key_info) {
                if (is_array($data)) {
                    $data = "array()";
                }
                $error = "<b>$new_part</b>: la clé <b>$key</b> ($data) n'est pas attendu";
                $this->last_error[] = $error;
                continue;
            }
            $type_finded = $this->verifType($data, $key_info, $new_part, $key);
            if (! $type_finded) {
                continue;
            }
            if ($type_finded == 'list' || $type_finded == 'associative_array') {
                $this->validatePart($key_info['key_name'], $data, $new_part);
            }
        }
        return ! $this->last_error;
    }

    private function verifType($data, $key_info, $new_part, $key)
    {
        $type_expected = $key_info['type'];
        $type_finded = $this->getDataType($data);
        if ($type_expected == 'choice') {
            $type_expected = 'string';
            if (! in_array($data, $key_info['choice'])) {
                $value = implode(',', $key_info['choice']);
                $this->last_error[] = "<b>$new_part:$key</b>  doit être une des valeurs suivante : $value - $data trouvé";
                return false;
            }
        }
        if ($type_expected == 'list_or_associative_array') {
            if ($type_finded == 'associative_array' || $type_finded == 'list') {
                return $type_finded;
            }
        }
        if ($type_expected == 'associative_array' || $type_expected == 'list') {
            if ($data == '') {
                return $type_expected;
            }
        }
        if ($type_expected == 'string_or_boolean') {
            if ($type_finded == 'string' || $type_finded == 'boolean') {
                return $type_finded;
            }
        }
        if ($type_finded != $type_expected) {
            $this->last_error[] = "<b>$new_part:$key</b> doit être de type <b>$type_expected</b> - $type_finded trouvé";
            return false;
        }
        return $type_finded;
    }

    private function getPossibleKeyInfo($part, $key)
    {
        if (empty($this->module_definition[$part])) {
            $this->last_error[] = "Erreur dans le fichier module-definiton.yml: la clé <b>$part</b> n'est pas défini";
            return false;
        }
        if (isset($this->module_definition[$part]['possible_key'][$key])) {
            $result = $this->module_definition[$part]['possible_key'][$key];
            $result['key_name'] = $key;
            return $result;
        }
        if (mb_substr($key, 0, 3) == 'or_') {
            if (isset($this->module_definition[$part]['possible_key']['or_X'])) {
                $result = $this->module_definition[$part]['possible_key']['or_X'];
                return $result;
            }
        }
        if (mb_substr($key, 0, 4) == 'and_') {
            if (isset($this->module_definition[$part]['possible_key']['and_X'])) {
                $result = $this->module_definition[$part]['possible_key']['and_X'];
                return $result;
            }
        }
        if (mb_substr($key, 0, 3) == 'no_') {
            if (isset($this->module_definition[$part]['possible_key']['no_X'])) {
                $result = $this->module_definition[$part]['possible_key']['no_X'];
                return $result;
            }
        }
        if (isset($this->module_definition[$part]['possible_key']['*'])) {
            $result = $this->module_definition[$part]['possible_key']['*'];
            return $result;
        }
        return false;
    }

    private function getDataType($data)
    {
        if (is_array($data)) {
            if ((bool)count(array_filter(array_keys($data), 'is_string'))) {
                return 'associative_array';
            }
             return 'list';
        }
        if (is_string($data)) {
            return 'string';
        }
        if (is_bool($data)) {
            return 'boolean';
        }
        return false;
    }
}
