<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum ActionElement: string
{
    case ACCUSE_DE_RECEPTION_ACTION = 'accuse_de_reception_action';
    case ACTION_AUTOMATIQUE = 'action-automatique';
    case ACTION_CLASS = 'action-class';
    case ACTION_SELECTION = 'action-selection';
    case CONNECTEUR_TYPE = 'connecteur-type';
    case CONNECTEUR_TYPE_ACTION = 'connecteur-type-action';
    case CONNECTEUR_TYPE_MAPPING = 'connecteur-type-mapping';
    case EDITABLE_CONTENT = 'editable-content';
    case MODIFICATION_NO_CHANGE_ETAT = 'modification-no-change-etat';
    case NAME_ACTION = 'name-action';
    case NO_WORKFLOW = 'no-workflow';
    case NUM_SAME_CONNECTEUR = 'num-same-connecteur';
    case PAS_DANS_UN_LOT = 'pas-dans-un-lot';
    case RULE = 'rule';
    case TYPE_ID_E = 'type_id_e';
    case WARNING = 'warning';
}
