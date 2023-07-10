<?php

declare(strict_types=1);

namespace Pastell\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DocumentTypeConfiguration implements ConfigurationInterface
{
    public const TYPE = 'type';
    public const NAME = 'name';
    public const DROIT_ID_U = 'droit_id_u';
    public const LAST_ACTION = 'last-action';
    public const HAS_ACTION = 'has-action';
    public const NO_ACTION = 'no-action';
    public const DOCUMENT_IS_VALID = 'document_is_valide';
    public const LINK_NAME = 'link_name';
    public const DEFAULT = 'default';
    public const INDEX = 'index';
    public const REQUIS = 'requis';
    public const PREG_MATCH = 'preg_match';
    public const PREG_MATCH_ERROR = 'preg_match_error';
    public const COMMENTAIRE = 'commentaire';
    public const TITLE = 'title';
    public const MULTIPLE = 'multiple';
    public const VISIONNEUSE = 'visionneuse';
    public const VISIONNEUSE_NO_LINK = 'visionneuse-no-link';
    public const CHOICE_ACTION = 'choice-action';
    public const ONCHANGE = 'onchange';
    public const READ_ONLY = 'read-only';
    public const EDIT_ONLY = 'edit-only';
    public const AUTOCOMPLETE = 'autocomplete';
    public const MAY_BE_NULL = 'may_be_null';
    public const IS_EQUAL = 'is_equal';
    public const IS_EQUAL_ERROR = 'is_equal_error';
    public const DEPEND = 'depend';
    public const NO_SHOW = 'no-show';
    public const CONTENT_TYPE = 'content-type';
    public const MAX_FILE_SIZE = 'max_file_size';
    public const MAX_MULTIPLE_FILE_SIZE = 'max_multipe_file_size';
    public const PROGRESS_BAR = 'progress_bar';
    public const SHOW_ROLE = 'show-role';
    public const READ_ONLY_CONTENT = 'read-only-content';
    public const FORMULAIRE = 'formulaire';
    public const MODULE = 'module';
    public const NOM = 'nom';
    public const DESCRIPTION = 'description';
    public const RESTRICTION_PACK = 'restriction_pack';
    public const CONNECTEUR = 'connecteur';
    public const AFFICHE_ONE = 'affiche_one';
    public const CHAMPS_AFFICHES = 'champs-affiches';
    public const CHAMPS_RECHERCHE_AVANCEE = 'champs-recherche-avancee';
    public const THRESHOLD_SIZE = 'threshold_size';
    public const THRESHOLD_FIELDS = 'threshold_fields';
    public const STUDIO_DEFINITION = 'studio_definition';
    public const VALUE = 'value';
    public const PAGE_CONDITION = 'page-condition';
    public const ACTION = 'action';
    public const NAME_ACTION = 'name-action';
    public const ACTION_CLASS = 'action-class';
    public const WARNING = 'warning';
    public const ACTION_AUTOMATIQUE = 'action-automatique';
    public const EDITABLE_CONTENT = 'editable-content';
    public const TYPE_ID_E = 'type_id_e';
    public const ACCUSE_DE_RECEPTION_ACTION = 'accuse_de_reception_action';
    public const PAS_DANS_UN_LOT = 'pas-dans-un-lot';
    public const NUM_SAME_CONNECTEUR = 'num-same-connecteur';
    public const ACTION_SELECTION = 'action-selection';
    public const MODIFICATION_NO_CHANGE_ETAT = 'modification-no-change-etat';
    public const NO_WORKFLOW = 'no-workflow';
    public const CONNECTEUR_TYPE = 'connecteur-type';
    public const CONNECTEUR_TYPE_ACTION = 'connecteur-type-action';
    public const RULE = 'rule';
    public const NO_LAST_ACTION = 'no-last-action';
    public const CONNECTEUR_TYPE_MAPPING = 'connecteur-type-mapping';
    public const CONTENT = 'content';
    private const ROLE_ID_E = 'role_id_e';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::MODULE);
        $treeBuilder->getRootNode()
            ->info("Le fichier definition.yml contient la définition d'un module Pastell")
            ->normalizeKeys(false)
            ->children()
                ->scalarNode(self::NOM)
                    ->info("Identifiant du module tel qu'il sera présenté aux utilisateurs.")
                ->end()
                ->scalarNode(self::TYPE)
                    ->info('Type de module. Utiliser pour classer les modules les uns par rapport aux autres.')
                    ->defaultValue('Types de dossier génériques')
                ->end()
                ->scalarNode(self::DESCRIPTION)
                    ->info('Permet de décrire le module')
                ->end()
                ->arrayNode(self::RESTRICTION_PACK)
                    ->info("Liste des restrictions d'utilisation pack (pack_chorus_pro, pack_marche...)")
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::CONNECTEUR)
                    ->info('Liste des types de connecteur utilisés par le module')
                    ->scalarPrototype()->end()
                ->end()
                ->append($this->addFormulaireNode())
                ->append($this->addPageConditionNode())
                ->append($this->addActionNode())
                ->booleanNode(self::AFFICHE_ONE)
                    ->info("Permet d'afficher l'ensemble des onglets sur une seule page "
                            . '(en mode affichage, les onglets sont conservés en mode édition)')
                    ->defaultFalse()
                ->end()
                ->arrayNode(self::CHAMPS_AFFICHES)
                    ->info('Liste des champs à afficher dans la liste des dossiers')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::CHAMPS_RECHERCHE_AVANCEE)
                    ->info('Liste des champs à afficher dans la recherche avancée')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode(self::THRESHOLD_SIZE)
                    ->info('Taille limite acceptée de tous les fichiers du dossier cumulés en octet. '
                            . '(optionnel, sans limite par défaut)')
                ->end()
                ->arrayNode(self::THRESHOLD_FIELDS)
                    ->info('Liste des champs de type file utilisés dans le calcul à la limite '
                            . 'définie par la clé threshold_size (optionnel, tous les champs `file` par défaut)')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode(self::STUDIO_DEFINITION)
                    ->info('Permet de préciser le contenu orginal du flux studio qui a permis la création '
                            . 'de ce type de dossier (en JSON)')
                ->end()
            ->end();
        return $treeBuilder;
    }

    private function addFormulaireNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::FORMULAIRE);
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
                        ->scalarNode(self::LINK_NAME)
                            ->info('Uniquement pour le type externalData, afin de mettre un texte sur le lien')
                        ->end()
                        ->scalarNode(self::DEFAULT)
                            ->info('Valeur initiale prise par le champs lors de la création du document.'
                                    . "\nPour une date, peut prendre en compte les chaînes de caractères "
                                    . 'compatibles avec la fonction PHP strtotime()'
                                    . "\n(Exemple: -30days), peut prendre la valeur \"empty\" "
                                    . 'pour spécifier de ne pas renseigner la date (sinon now par défaut)')
                        ->end()
                        ->booleanNode(self::INDEX)
                            ->info('Indique si le champs est indexé par la base de données')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode(self::REQUIS)->end()
                        ->scalarNode(self::PREG_MATCH)->end()
                        ->scalarNode(self::PREG_MATCH_ERROR)->end()
                        ->scalarNode(self::COMMENTAIRE)
                            ->info('Le commentaire est affiché comme aide en mode édition')
                        ->end()
                        ->booleanNode(self::TITLE)
                            ->info('Information enregistré dans la base de donnée pour identifier le document')
                        ->end()
                        ->booleanNode(self::MULTIPLE)
                            ->info('uniquement pour le type file')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode(self::VISIONNEUSE)
                            ->info('Permet de spécifier une classe utilisé pour visualiser le ou les fichiers')
                        ->end()
                        ->booleanNode(self::VISIONNEUSE_NO_LINK)
                            ->info("Le lien pour télécharger le fichier n'est pas affiché\n"
                                    . "Ne fait rien si la propriété visionneuse n'est pas utilisée.")
                        ->end()
                        ->scalarNode(self::CHOICE_ACTION)
                            ->info('Pointeur vers une action')
                        ->end()
                        ->scalarNode(self::ONCHANGE)->end()
                        ->booleanNode(self::READ_ONLY)
                            ->info('Le champ ne sera pas affiché en mode édition, mais seulement en mode affichage')
                        ->end()
                        ->booleanNode(self::EDIT_ONLY)
                            ->info('Le champ ne sera pas affiché en mode affichage, mais seulement en mode édition')
                        ->end()
                        ->scalarNode(self::AUTOCOMPLETE)->end()
                        ->booleanNode(self::MAY_BE_NULL)->end()
                        ->scalarNode(self::IS_EQUAL)->end()
                        ->scalarNode(self::IS_EQUAL_ERROR)->end()
                        ->scalarNode(self::DEPEND)
                            ->info("champs multiple dépendant d'un champ de type file (multiple)")
                        ->end()
                        ->booleanNode(self::NO_SHOW)
                            ->info("Le champs ne sera pas affiché (ni en mode affichage, ni en mode d'édition)")
                        ->end()
                        ->scalarNode(self::CONTENT_TYPE)
                            ->info("Permet de spécifier une liste de content-type séparé par une virgule.\n"
                                . "Ne fonctionne que pour le type fichier.\n"
                                . 'Indique que le ou les fichiers doivent avoir un content-type présent '
                                . "dans la liste (sinon, le document n'est pas valide).\n"
                                . "La liste des content-type est maintenue par l'IANA : "
                                . 'https://www.iana.org/assignments/media-types/media-types.xhtml')
                        ->end()
                        ->scalarNode(self::MAX_FILE_SIZE)
                            ->info('Taille maximale du fichier en octet (optionnel,sans limite par défaut)')
                        ->end()
                        ->scalarNode(self::MAX_MULTIPLE_FILE_SIZE)
                          ->info('Taille maximale des fichiers du champ multiple '
                                  . '(optionnel,sans limite par défaut, cumulable avec max_file_size)')
                          ->end()
                        ->booleanNode(self::PROGRESS_BAR)
                            ->info('NE PLUS UTILISER - déprécié PA 3.0. Sur les champs de type fichier, permet '
                                . "d'ajouter une barre de progression (systématique a partir de la version 2.1.0)")
                        ->end()
                        ->arrayNode(self::SHOW_ROLE)
                          ->info("N'affiche cette information que pour certain role")
                          ->end()
                        ->arrayNode(self::READ_ONLY_CONTENT)
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
        $treeBuilder = new TreeBuilder(self::VALUE);
        $treeBuilder->getRootNode()
            ->info('uniquement pour le type select')
            ->scalarPrototype()
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addPageConditionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::PAGE_CONDITION);
        $treeBuilder->getRootNode()
            ->info('Détermine les règles permettant de savoir si une page doit être affiché ou non')
            ->arrayPrototype()
                ->booleanPrototype()
                ->end()
                ->scalarPrototype()
                ->end()
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addActionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::ACTION);
        $treeBuilder->getRootNode()
            ->info("Définition de l'ensemble des actions qui peuvent être déclenché sur le dossier")
            ->defaultValue(["pas d'action"])
            ->arrayPrototype()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode(self::NAME)
                    ->info("Nom de l'action telle qu'elle apparait une fois réalisé (Envoyé)")
                ->end()
                ->scalarNode(self::NAME_ACTION)
                    ->info("Nom de l'action qui apparait sur les boutons de déclenchement des action (Envoyer)")
                ->end()
                ->scalarNode(self::ACTION_CLASS)->end()
                ->scalarNode('connecteur-type-data-seda-class-name')
                    ->info('Permet de spécifier le nom de la classe appelé pour la génération du bordereau SEDA')
                ->end()
                ->scalarNode(self::WARNING)
                    ->info('Si présent, une page intermédiaire avec confirmation du choix apparaît')
                ->end()
                ->scalarNode(self::ACTION_AUTOMATIQUE)->end()
                ->arrayNode(self::EDITABLE_CONTENT)
                    ->info('Identifiant des champs modifiables')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::TYPE_ID_E)
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode(self::ACCUSE_DE_RECEPTION_ACTION)
                  ->info("l'action nécessite un accusé de réception avant d'être réalisé.")
                ->end()
                ->booleanNode(self::PAS_DANS_UN_LOT)
                  ->info('cette action ne peut pas être réalisée dans le cadre du traitement par lot')
                ->end()
                ->scalarNode(self::NUM_SAME_CONNECTEUR)
                    ->info('Si le flux utilise plusieurs connecteurs du même type, '
                            . "numéro d'ordre du connecteur à utiliser pour l'action considérée (débute à 0)")
                  ->defaultValue('0')
                ->end()
                ->scalarNode(self::ACTION_SELECTION)
                    ->info("l'action nécessite de choisir dans une liste d'entité spécifique. "
                            . "Ici, le type de l'entité spécifique")
                ->end()
                ->booleanNode(self::MODIFICATION_NO_CHANGE_ETAT)
                    ->info('Si true, alors après une modification du document, si celui-ci est dans cet état, '
                            . "alors il ne changera pas d'état")
                    ->defaultFalse()
                ->end()
                ->booleanNode(self::NO_WORKFLOW)->end()
                ->scalarNode(self::CONNECTEUR_TYPE)
                    ->info("Pour l'action standard indique dans quel type de connecteur l'action doit-être executée")
                ->end()
                ->scalarNode(self::CONNECTEUR_TYPE_ACTION)
                    ->info("Permet de spécifier le nom de la classe à executé dans le cadre de l'action standard")
                ->end()
                ->append($this->addRuleNode())
                ->append($this->addConnecteurTypeMappingNode())
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addRuleNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::RULE);
        $treeBuilder->getRootNode()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode(self::NO_LAST_ACTION)
                    ->info("si présent, il s'agit d'une action initiale")
                ->end()
                ->scalarNode(self::DROIT_ID_U)->end()
                ->arrayNode(self::TYPE_ID_E)
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
                ->scalarNode(self::ROLE_ID_E)->end()
                ->append($this->addOr1Node())
                ->append($this->addContentNode())
            ->end();
        return $treeBuilder->getRootNode();
    }

    private function addConnecteurTypeMappingNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::CONNECTEUR_TYPE_MAPPING);
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
        $treeBuilder = new TreeBuilder(self::CONTENT);
        $treeBuilder->getRootNode()
            ->booleanPrototype()->end()
            ->scalarPrototype()->end()
            ->end();
        return $treeBuilder->getRootNode();
    }
}
