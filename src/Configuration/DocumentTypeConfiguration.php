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
            ->info("Le fichier definition.yml contient la définition d'un module Pastell")
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('nom')
                    ->info("Identifiant du module tel qu'il sera présenté aux utilisateurs.")
                ->end()
                ->scalarNode(self::TYPE)
                    ->info('Type de module. Utiliser pour classer les modules les uns par rapport aux autres.')
                    ->defaultValue('Types de dossier génériques')
                ->end()
                ->scalarNode('description')
                    ->info('Permet de décrire le module')
                ->end()
                ->arrayNode('restriction_pack')
                    ->info("Liste des restrictions d'utilisation pack (pack_chorus_pro, pack_marche...)")
                    ->defaultValue(['aucune restriction'])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('connecteur')
                    ->info('Liste des types de connecteur utilisés par le module')
                    ->defaultValue(['aucun connecteur'])
                    ->scalarPrototype()->end()
                ->end()
                ->append($this->addFormulaireNode())
                ->append($this->addPageConditionNode())
                ->append($this->addActionNode())
                ->booleanNode('affiche_one')
                    ->info("Permet d'afficher l'ensemble des onglets sur une seule page "
                            . '(en mode affichage, les onglets sont conservés en mode édition)')
                    ->defaultFalse()
                ->end()
                ->arrayNode('champs-affiches')
                    ->info('Liste des champs à afficher dans la liste des dossiers')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('champs-recherche-avancee')
                    ->info('Liste des champs à afficher dans la recherche avancée')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('threshold_size')
                    ->info('Taille limite acceptée de tous les fichiers du dossier cumulés en octet. '
                            . '(optionnel, sans limite par défaut)')
                ->end()
                ->arrayNode('threshold_fields')
                    ->info('Liste des champs de type file utilisés dans le calcul à la limite '
                            . 'définie par la clé threshold_size (optionnel, tous les champs `file` par défaut)')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('studio_definition')
                    ->info('Permet de préciser le contenu orginal du flux studio qui a permis la création '
                            . 'de ce type de dossier (en JSON)')
                ->end()
            ->end();
        return $treeBuilder;
    }

    private function addFormulaireNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('formulaire');
        $treeBuilder->getRootNode()
            ->info("Définition du formulaire permettant la création et l'évolution du dossier")
            ->defaultValue(['pas de formulaire'])
            ->arrayPrototype()
                ->info("le formulaire est composé d'un ensemble d'onglets")
                ->arrayPrototype()
                    ->info("un onglet est composé d'élement de formulaire")
                    ->normalizeKeys(false)
                    ->children()
                        ->scalarNode(self::NAME)->end()
                        ->enumNode(self::TYPE)
                            ->values(array_column(ElementType::cases(), 'value'))
                        ->end()
                        ->scalarNode('link_name')
                            ->info('Uniquement pour le type externalData, afin de mettre un texte sur le lien')
                        ->end()
                        ->scalarNode('default')
                            ->info('Valeur initiale prise par le champs lors de la création du document.'
                                    . "\nPour une date, peut prendre en compte les chaînes de caractères "
                                    . 'compatibles avec la fonction PHP strtotime()'
                                    . "\n(Exemple: -30days), peut prendre la valeur \"empty\" "
                                    . 'pour spécifier de ne pas renseigner la date (sinon now par défaut)')
                        ->end()
                        ->booleanNode('index')
                            ->info('Indique si le champs est indexé par la base de données')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('requis')->end()
                        ->scalarNode('preg_match')->end()
                        ->scalarNode('preg_match_error')->end()
                        ->scalarNode('commentaire')
                            ->info('Le commentaire est affiché comme aide en mode édition')
                        ->end()
                        ->booleanNode('title')
                            ->info('Information enregistré dans la base de donnée pour identifier le document')
                        ->end()
                        ->booleanNode('multiple')
                            ->info('uniquement pour le type file')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('visionneuse')
                            ->info('Permet de spécifier une classe utilisé pour visualiser le ou les fichiers')
                        ->end()
                        ->booleanNode('visionneuse-no-link')
                            ->info("Le lien pour télécharger le fichier n'est pas affiché\n"
                                    . "Ne fait rien si la propriété visionneuse n'est pas utilisée.")
                        ->end()
                        ->scalarNode('choice-action')
                            ->info('Pointeur vers une action')
                        ->end()
                        ->scalarNode('onchange')->end()
                        ->booleanNode('read-only')
                            ->info('Le champ ne sera pas affiché en mode édition, mais seulement en mode affichage')
                        ->end()
                        ->booleanNode('edit-only')
                            ->info('Le champ ne sera pas affiché en mode affichage, mais seulement en mode édition')
                        ->end()
                        ->scalarNode('autocomplete')->end()
                        ->booleanNode('may_be_null')->end()
                        ->scalarNode('is_equal')->end()
                        ->scalarNode('is_equal_error')->end()
                        ->scalarNode('depend')
                            ->info("champs multiple dépendant d'un champ de type file (multiple)")
                        ->end()
                        ->booleanNode('no-show')
                            ->info("Le champs ne sera pas affiché (ni en mode affichage, ni en mode d'édition)")
                        ->end()
                        ->scalarNode('content-type')
                            ->info("Permet de spécifier une liste de content-type séparé par une virgule.\n"
                                . "Ne fonctionne que pour le type fichier.\n"
                                . 'Indique que le ou les fichiers doivent avoir un content-type présent '
                                . "dans la liste (sinon, le document n'est pas valide).\n"
                                . "La liste des content-type est maintenue par l'IANA : "
                                . 'https://www.iana.org/assignments/media-types/media-types.xhtml')
                        ->end()
                        ->scalarNode('max_file_size')
                            ->info('Taille maximale du fichier en octet (optionnel,sans limite par défaut)')
                        ->end()
                        ->scalarNode('max_multipe_file_size')
                          ->info('Taille maximale des fichiers du champ multiple '
                                  . '(optionnel,sans limite par défaut, cumulable avec max_file_size)')
                          ->end()
                        ->booleanNode('progress_bar')
                            ->info('NE PLUS UTILISER - déprécié PA 3.0. Sur les champs de type fichier, permet '
                                . "d'ajouter une barre de progression (systématique a partir de la version 2.1.0)")
                        ->end()
                        ->arrayNode('show-role')
                          ->info("N'affiche cette information que pour certain role")
                          ->end()
                        ->arrayNode('read-only-content')
                          ->info("(ne pas utiliser) permet de rendre un champs éditable n'importe quand "
                                  . "si une valeur est vérifié.\n"
                                  . "Exemple: \"has_reponse_lettre_courrier_simple: true\".\n"
                                  . 'Dans la plupart des cas, editable-content sur un état suffit.')
                          ->end()
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
            ->info('uniquement pour le type select')
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
            ->info('Détermine les règles permettant de savoir si une page doit être affiché ou non')
            ->defaultValue(['pas de condition'])
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
            ->info("Définition de l'ensemble des actions qui peuvent être déclenché sur le dossier")
            ->defaultValue(["pas d'action"])
            ->arrayPrototype()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode(self::NAME)
                    ->info("Nom de l'action telle qu'elle apparait une fois réalisé (Envoyé)")
                ->end()
                ->scalarNode('name-action')
                    ->info("Nom de l'action qui apparait sur les boutons de déclenchement des action (Envoyer)")
                ->end()
                ->scalarNode('action-class')->end()
                ->scalarNode('connecteur-type-data-seda-class-name')
                    ->info('Permet de spécifier le nom de la classe appelé pour la génération du bordereau SEDA')
                ->end()
                ->scalarNode('warning')
                    ->info('Si présent, une page intermédiaire avec confirmation du choix apparaît')
                ->end()
                ->scalarNode('action-automatique')->end()
                ->arrayNode('editable-content')
                    ->info('Identifiant des champs modifiables')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('type_id_e')
                    ->scalarPrototype()->end()
                ->end()
              ->scalarNode('accuse_de_reception_action')
                  ->info("l'action nécessite un accusé de réception avant d'être réalisé.")
              ->end()
              ->booleanNode('pas-dans-un-lot')
                  ->info('cette action ne peut pas être réalisée dans le cadre du traitement par lot')
              ->end()
              ->scalarNode('num-same-connecteur')
                    ->info('Si le flux utilise plusieurs connecteurs du même type, '
                            . "numéro d'ordre du connecteur à utiliser pour l'action considérée (débute à 0)")
                  ->defaultValue('0')
              ->end()
                ->scalarNode('action-selection')
                    ->info("l'action nécessite de choisir dans une liste d'entité spécifique. "
                            . "Ici, le type de l'entité spécifique")
                ->end()
                ->booleanNode('modification-no-change-etat')
                    ->info('Si true, alors après une modification du document, si celui-ci est dans cet état, '
                            . "alors il ne changera pas d'état")
                    ->defaultFalse()
                ->end()
                ->booleanNode('no-workflow')->end()
                ->scalarNode('connecteur-type')
                    ->info("Pour l'action standard indique dans quel type de connecteur l'action doit-être executée")
                ->end()
                ->scalarNode('connecteur-type-action')
                    ->info("Permet de spécifier le nom de la classe à executé dans le cadre de l'action standard")
                ->end()
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
                ->scalarNode('no-last-action')
                    ->info("si présent, il s'agit d'une action initiale")
                ->end()
                ->scalarNode(self::DROIT_ID_U)->end()
                ->arrayNode('type_id_e')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::LAST_ACTION)
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::HAS_ACTION)
                    ->info('vrai si le document est passé par une des actions')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::NO_ACTION)
                    ->info('faux si le document est passé par toutes les actions listées')
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
            ->info("Permet de spécifier le mapping entre les noms des élements du document\n"
                    . "et les noms des élements attendu par l'action du connecteur type")
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
