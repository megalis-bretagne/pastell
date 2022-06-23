<?php

namespace Pastell\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConnectorConfiguration implements ConfigurationInterface
{
    public const NOM = 'nom';
    public const TYPE = 'type';
    public const DESCRIPTION = 'description';
    public const RESTRICTION_PACK = 'restriction_pack';
    public const HERITAGE = 'heritage';

    public const FORMULAIRE = 'formulaire';
    public const PAGE_NAME = 'page_name';

    public const ELEMENT_ID = 'element_id';
    public const ELEMENT_NAME = 'name';
    public const ELEMENT_COMMENT = 'commentaire';
    public const ELEMENT_TYPE = 'type';
    public const ELEMENT_VALUE = 'value';
    public const ELEMENT_IS_MULTIPLE = 'multiple';
    public const ELEMENT_DEPEND = 'depend';
    public const ELEMENT_DEFAULT = 'default';
    public const ELEMENT_ONCHANGE = 'onchange';
    public const ELEMENT_NO_SHOW = 'no-show';
    public const ELEMENT_READ_ONLY = 'read-only';
    public const ELEMENT_EDIT_ONLY = 'edit-only';
    public const ELEMENT_CHOICE_ACTION = 'choice-action';
    public const ELEMENT_LINK_NAME = 'link_name';
    public const ELEMENT_CONTENT_TYPE = 'content-type';
    public const ELEMENT_REQUIS = 'requis';
    public const ELEMENT_VISIONNEUSE = 'visionneuse';
    public const ELEMENT_SCRIPT = 'script';
    public const ELEMENT_PREG_MATCH = 'preg_match';
    public const ELEMENT_PREG_MATCH_ERROR = 'preg_match_error';

    public const ACTION = 'action';
    public const ACTION_ID = 'action_id';
    public const ACTION_NAME = 'name';
    public const ACTION_CLASS = 'action_class';
    public const ACTION_RULE = 'rule';
    public const ACTION_RULE_USER_PERMISSION = 'droit_id_u';
    public const ACTION_RULE_ENTITY_ROLE = 'role_id_e';
    public const ACTION_AUTOMATIQUE = 'action_automatique';
    public const ACTION_CONNECTEUR_TYPE = 'connecteur_type';
    public const ACTION_CONNECTEUR_TYPE_ACTION = 'connecteur_type_action';
    public const ACTION_CONNECTEUR_TYPE_MAPPING = 'connecteur_type_mapping';
    public const ACTION_NO_WORKFLOW = 'no_workflow';
    public const ACTION_CONNECTEUR_TYPE_ELEMENT_ID = 'element_id';

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('connector');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode(self::NOM)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Libellé du connecteur')
                ->end()
                ->scalarNode(self::TYPE)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Famille du connecteur')
                ->end()
                ->scalarNode(self::DESCRIPTION)
                    ->info('Description du connecteur')
                ->end()
                ->scalarNode(self::HERITAGE)
                    ->info('Fichier YML dont hérite le présent fichier')
                ->end()
                ->arrayNode(self::RESTRICTION_PACK)
                    ->info("Indique que le connecteur fait partie d'un des packs")
                    ->scalarPrototype()
                    ->end()
                ->end()
                ->append($this->addFormulaireNode())
                ->append($this->addActionNode())
            ->end()
        ;
        return $treeBuilder;
    }

    private function addFormulaireNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::FORMULAIRE);
        $treeBuilder->getRootNode()
            ->useAttributeAsKey(self::PAGE_NAME)
            ->arrayPrototype()
                ->useAttributeAsKey(self::ELEMENT_ID)
                    ->arrayPrototype()            ->normalizeKeys(false)
                        ->children()
                            ->scalarNode(self::ELEMENT_NAME)
                            ->end()
                            ->scalarNode(self::ELEMENT_COMMENT)
                            ->end()
                            ->enumNode(self::ELEMENT_TYPE)
                                ->values(array_column(ElementType::cases(), 'value'))
                                ->defaultValue('text')
                            ->end()
                            ->arrayNode(self::ELEMENT_VALUE)
                                ->scalarPrototype()
                                ->end()
                            ->end()
                            ->scalarNode(self::ELEMENT_IS_MULTIPLE)
                            ->end()
                            ->scalarNode(self::ELEMENT_DEPEND)
                                ->setDeprecated('libriciel/pastell', '4.0.0')
                            ->end()
                            ->scalarNode(self::ELEMENT_DEFAULT)
                            ->end()
                            ->scalarNode(self::ELEMENT_ONCHANGE)
                            ->end()
                            ->booleanNode(self::ELEMENT_NO_SHOW)
                            ->end()
                            ->booleanNode(self::ELEMENT_READ_ONLY)
                            ->end()
                            ->booleanNode(self::ELEMENT_EDIT_ONLY)
                            ->end()
                            ->scalarNode(self::ELEMENT_CHOICE_ACTION) //TODO à vérifier
                            ->end()
                            ->scalarNode(self::ELEMENT_LINK_NAME)
                            ->end()
                            ->scalarNode(self::ELEMENT_CONTENT_TYPE)
                            ->end()
                            ->scalarNode(self::ELEMENT_REQUIS)
                            ->end()
                            ->scalarNode(self::ELEMENT_VISIONNEUSE) //TODO à vérifier
                            ->end()
                            ->scalarNode(self::ELEMENT_SCRIPT) //TODO à vérifier (WTF ?)
                            ->end()
                            ->scalarNode(self::ELEMENT_PREG_MATCH) //TODO à vérifier
                            ->end()
                            ->scalarNode(self::ELEMENT_PREG_MATCH_ERROR)
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ->end();
        return $treeBuilder->getRootNode();
    }

    private function addActionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::ACTION);
        $treeBuilder->getRootNode()
            ->useAttributeAsKey(self::ACTION_ID)
                ->arrayPrototype()
                    ->children()
                        ->scalarNode(self::ACTION_NAME)
                        ->end()
                        ->scalarNode(self::ACTION_CLASS) //TODO test if action exists
                        ->end()
                        ->arrayNode(self::ACTION_RULE)
                                ->children()
                                    ->scalarNode(self::ACTION_RULE_USER_PERMISSION) //TODO vérifier
                                    ->end()
                                    ->scalarNode(self::ACTION_RULE_ENTITY_ROLE) //TODO à vérifier
                                    ->end()
                                ->end()

                        ->end()
                        ->scalarNode(self::ACTION_AUTOMATIQUE)
                        ->end()
                        ->scalarNode(self::ACTION_CONNECTEUR_TYPE)
                        ->end()
                        ->scalarNode(self::ACTION_CONNECTEUR_TYPE_ACTION)
                        ->end()
                        ->booleanNode(self::ACTION_NO_WORKFLOW)
                        ->end()
                        ->arrayNode(self::ACTION_CONNECTEUR_TYPE_MAPPING)
                            ->useAttributeAsKey(self::ACTION_CONNECTEUR_TYPE_ELEMENT_ID)
                            ->scalarPrototype()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder->getRootNode();
    }
}
