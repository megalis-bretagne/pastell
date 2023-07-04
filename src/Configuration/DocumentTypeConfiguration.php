<?php

declare(strict_types=1);

namespace Pastell\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DocumentTypeConfiguration implements ConfigurationInterface
{
    private const TYPE = 'type';
    private const NAME = 'name';
    private const HAS_INFORMATION = 'has_information';
    private const HAS_FICHIER_CHORUS = 'has_fichier_chorus';
    private const SAE_TRANSFERT_ID = 'sae_transfert_id';
    private const ENVOI_SIGNATURE = 'envoi_signature';
    private const ENVOI_SIGNATURE_FAST = 'envoi_signature_fast';
    private const REPONDRE = 'repondre';
    private const ENVOI_MAILSEC = 'envoi_mailsec';
    private const ENVOI_SAE = 'envoi_sae';
    private const ENVOI_VISA = 'envoi_visa';
    private const DEPOT_TYPE = 'depot_type';
    private const DROIT_ID_U = 'droit_id_u';
    private const LAST_ACTION = 'last-action';
    private const HAS_ACTION = 'has-action';
    private const NO_ACTION = 'no-action';
    private const DOCUMENT_IS_VALID = 'document_is_valide';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('module');
        $treeBuilder->getRootNode()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('nom')->end()
                ->scalarNode(self::TYPE)->end()
                ->scalarNode('description')->end()
                ->arrayNode('restriction_pack')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('connecteur')
                    ->scalarPrototype()
                        ->defaultValue(['aucun connecteur'])
                    ->end()
                ->end()
                ->append($this->addFormulaireNode())
                ->append($this->addPageConditionNode())
                ->append($this->addActionNode())
                ->booleanNode('affiche_one')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('champs-affiches')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('champs-recherche-avancee')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('threshold_size')->end()
                ->arrayNode('threshold_fields')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('studio_definition')->end()
            ->end();
        return $treeBuilder;
    }

    private function addFormulaireNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('formulaire');
        $treeBuilder->getRootNode()
            ->arrayPrototype()
                ->arrayPrototype()
                ->normalizeKeys(false)
                    ->children()
                        ->scalarNode(self::NAME)->end()
                        ->enumNode(self::TYPE)
                            ->values(array_column(ElementType::cases(), 'value'))
                        ->end()
                        ->scalarNode('link_name')->end()
                        ->scalarNode('default')->end()
                        ->booleanNode('index')
                            ->defaultValue(false)
                        ->end()
                        ->booleanNode('requis')->end()
                        ->scalarNode('preg_match')->end()
                        ->scalarNode('preg_match_error')->end()
                        ->scalarNode('commentaire')->end()
                        ->booleanNode('title')->end()
                        ->booleanNode('multiple')
                            ->defaultValue(false)
                        ->end()
                        ->scalarNode('visionneuse')->end()
                        ->booleanNode('visionneuse-no-link')->end()
                        ->scalarNode('choice-action')->end()
                        ->scalarNode('onchange')->end()
                        ->booleanNode('read-only')->end()
                        ->booleanNode('edit-only')->end()
                        ->scalarNode('autocomplete')->end()
                        ->booleanNode('may_be_null')->end()
                        ->scalarNode('is_equal')->end()
                        ->scalarNode('is_equal_error')->end()
                        ->scalarNode('depend')->end()
                        ->booleanNode('no-show')->end()
                        ->scalarNode('content-type')->end()
                        ->scalarNode('max_file_size')->end()
                        ->scalarNode('max_multipe_file_size')->end()
                        ->booleanNode('progress_bar')->end()
                        ->arrayNode('show-role')->end()
                        ->arrayNode('read-only-content')->end()
                        ->append($this->addValueNode())
                    ->end()
                ->end()
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addValueNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('value');
        $treeBuilder->getRootNode()
            ->booleanPrototype()
            ->end()
            ->scalarPrototype()
            ->end();

        return $treeBuilder->getRootNode();
    }

    private function addPageConditionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('page-condition');
        $treeBuilder->getRootNode()
            ->arrayPrototype()
            ->children()
                ->booleanNode(self::HAS_INFORMATION)->end()
                ->booleanNode('has_donnees_chorus_pro')->end()
                ->booleanNode('has_donnees_chorus_pro_xml')->end()
                ->booleanNode(self::HAS_FICHIER_CHORUS)->end()
                ->booleanNode('has_donnees_depot')->end()
                ->booleanNode(self::SAE_TRANSFERT_ID)->end()
                ->booleanNode(self::ENVOI_SIGNATURE)->end()
                ->booleanNode(self::ENVOI_SIGNATURE_FAST)->end()
                ->booleanNode('signature_locale_display')->end()
                ->booleanNode('has_historique')->end()
                ->booleanNode('has_information_complementaire')->end()
                ->booleanNode('has_bordereau')->end()
                ->booleanNode('has_ged_document_id')->end()
                ->booleanNode('has_annulation')->end()
                ->booleanNode('has_reponse_prefecture')->end()
                ->booleanNode('has_transformation')->end()
                ->booleanNode(self::REPONDRE)->end()
                ->booleanNode('has_acquittement')->end()
                ->booleanNode('sae_show')->end()
                ->booleanNode('envoi_iparapheur')->end()
                ->booleanNode('envoi_fast')->end()
                ->booleanNode('has_signature')->end()
                ->booleanNode(self::ENVOI_MAILSEC)->end()
                ->booleanNode('envoi_tdt_actes')->end()
                ->booleanNode(self::ENVOI_SAE)->end()
                ->booleanNode(self::ENVOI_VISA)->end()
                ->booleanNode('journal_show')->end()
                ->scalarNode(self::DEPOT_TYPE)->end()
                ->scalarNode('type_reponse')->end()
                ->booleanNode('has_information_pes_aller')->end()
                ->booleanNode('has_reponse')->end()
                ->booleanNode('has_message')->end()
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addActionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('action');
        $treeBuilder->getRootNode()
            ->arrayPrototype()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode(self::NAME)->end()
                ->scalarNode('name-action')->end()
                ->scalarNode('action-class')->end()
                ->scalarNode('connecteur-type-data-seda-class-name')->end()
                ->scalarNode('warning')->end()
                ->scalarNode('action-automatique')->end()
                ->arrayNode('editable-content')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('type_id_e')
                    ->scalarPrototype()->end()
                ->end()
              ->scalarNode('accuse_de_reception_action')->end()
              ->booleanNode('pas-dans-un-lot')->end()
              ->scalarNode('num-same-connecteur')
                  ->defaultValue('0')
              ->end()
                ->scalarNode('action-selection')->end()
                ->booleanNode('modification-no-change-etat')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('no-workflow')->end()
                ->scalarNode('connecteur-type')->end()
                ->scalarNode('connecteur-type-action')->end()
                ->append($this->addRuleNode())
                ->append($this->addConnecteurTypeMappingNode())
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addRuleNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('rule');
        $treeBuilder->getRootNode()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('no-last-action')->end()
                ->scalarNode(self::DROIT_ID_U)->end()
                ->arrayNode('type_id_e')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::LAST_ACTION)
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::HAS_ACTION)
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::NO_ACTION)
                    ->scalarPrototype()->end()
                ->end()
                ->booleanNode(self::DOCUMENT_IS_VALID)->end()
                ->scalarNode('role_id_e')->end()
                ->append($this->addOr1Node())
                ->append($this->addContentNode())
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addConnecteurTypeMappingNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('connecteur-type-mapping');
        $treeBuilder->getRootNode()
            ->scalarPrototype()
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addOr1Node(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('or_1');
        $treeBuilder->getRootNode()
            ->arrayPrototype()
            ->normalizeKeys(false)
            ->children()
            ->arrayNode(self::LAST_ACTION)->scalarPrototype()->end()->end()
            ->arrayNode(self::HAS_ACTION)->scalarPrototype()->end()->end()
            ->arrayNode(self::NO_ACTION)->scalarPrototype()->end()->end()
            ->booleanNode(self::DOCUMENT_IS_VALID)->end()
            ->scalarNode(self::DROIT_ID_U)->end()
            ->end()
            ->append($this->addNo1Node())
            ->append($this->addContentNode())
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addNo1Node(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('no_1');
        $treeBuilder->getRootNode()
            ->normalizeKeys(false)
            ->children()
            ->arrayNode(self::HAS_ACTION)->scalarPrototype()->end()->end()
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addContentNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('content');
        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('envoi_tdt')->end()
                ->booleanNode(self::ENVOI_SIGNATURE)->end()
                ->booleanNode(self::ENVOI_SIGNATURE_FAST)->end()
                ->booleanNode('has_signature_locale')->end()
                ->booleanNode('signature')->end()
                ->booleanNode(self::ENVOI_SAE)->end()
                ->booleanNode('envoi_ged')->end()
                ->booleanNode('classification')->end()
                ->booleanNode(self::HAS_INFORMATION)->end()
                ->scalarNode(self::DEPOT_TYPE)->end()
                ->scalarNode(self::HAS_FICHIER_CHORUS)->end()
                ->booleanNode(self::REPONDRE)->end()
                ->booleanNode(self::ENVOI_MAILSEC)->end()
                ->booleanNode('has_mise_a_dispo_gf')->end()
                ->booleanNode('is_cpp')->end()
                ->booleanNode('is_annule')->end()
                ->booleanNode('statut_cible_liste')->end()
                ->booleanNode(self::ENVOI_VISA)->end()
                ->booleanNode('has_send_ged')->end()
            ->end();
        return $treeBuilder->getRootNode();
    }
}
