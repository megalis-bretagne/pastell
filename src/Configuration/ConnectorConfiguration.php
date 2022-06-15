<?php

namespace Pastell\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConnectorConfiguration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('connector');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('nom')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Libellé du connecteur')
                ->end()
                ->scalarNode('type')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Famille du connecteur')
                ->end()
                ->scalarNode('description')
                    ->info('Description du connecteur')
                ->end()
                ->scalarNode('heritage')
                    ->info('Fichier YML dont hérite le présent fichier')
                ->end()
                ->arrayNode('restriction_pack')
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
        $treeBuilder = new TreeBuilder('formulaire');
        $treeBuilder->getRootNode()
            ->useAttributeAsKey('page_name')
            ->arrayPrototype()
                ->useAttributeAsKey('element_id')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                            ->end()
                            ->scalarNode('commentaire')
                            ->end()
                            ->enumNode('type')
                                ->values(['text', 'password', 'select', 'file', 'checkbox', 'externalData', 'textarea', 'date', 'link'])
                                ->defaultValue('text')
                            ->end()
                            ->arrayNode('value')
                                ->scalarPrototype()
                                ->end()
                            ->end()
                            ->scalarNode('multiple')
                            ->end()
                            ->scalarNode('depend')
                                ->setDeprecated('libriciel/pastell', '4.0.0')
                            ->end()
                            ->scalarNode('default')
                            ->end()
                            ->scalarNode('onchange')
                            ->end()
                            ->booleanNode('no_show')
                            ->end()
                            ->booleanNode('read_only')
                            ->end()
                            ->booleanNode('edit_only')
                            ->end()
                            ->scalarNode('choice_action') //TODO à vérifier
                            ->end()
                            ->scalarNode('link_name')
                            ->end()
                            ->scalarNode('content_type')
                            ->end()
                            ->scalarNode('requis')
                            ->end()
                            ->scalarNode('visionneuse') //TODO à vérifier
                            ->end()
                            ->scalarNode('script') //TODO à vérifier (WTF ?)
                            ->end()
                            ->scalarNode('preg_match') //TODO à vérifier
                            ->end()
                            ->scalarNode('preg_match_error')
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ->end();
        return $treeBuilder->getRootNode();
    }

    private function addActionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('action');
        $treeBuilder->getRootNode()
            ->useAttributeAsKey('action_id')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')
                        ->end()
                        ->scalarNode('action_class') //TODO test if action exists
                        ->end()
                        ->arrayNode('rule')
                                ->children()
                                    ->scalarNode('droit_id_u') //TODO vérifier
                                    ->end()
                                    ->scalarNode('role_id_e') //TODO à vérifier
                                    ->end()
                                ->end()

                        ->end()
                        ->scalarNode('action_automatique')
                        ->end()
                        ->scalarNode('connecteur_type')
                        ->end()
                        ->scalarNode('connecteur_type_action')
                        ->end()
                        ->booleanNode('no_workflow')
                        ->end()
                        ->arrayNode('connecteur_type_mapping')
                            ->useAttributeAsKey('element_id')
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
