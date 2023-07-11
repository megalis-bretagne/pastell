<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum ModuleElement: string
{
    case ACTION = 'action';
    case AFFICHE_ONE = 'affiche_one';
    case CHAMPS_AFFICHES = 'champs-affiches';
    case CHAMPS_RECHERCHE_AVANCEE = 'champs-recherche-avancee';
    case CONNECTEUR = 'connecteur';
    case DESCRIPTION = 'description';
    case FORMULAIRE = 'formulaire';
    case NOM = 'nom';
    case PAGE_CONDITION = 'page-condition';
    case RESTRICTION_PACK = 'restriction_pack';
    case STUDIO_DEFINITION = 'studio_definition';
    case THRESHOLD_FIELDS = 'threshold_fields';
    case THRESHOLD_SIZE = 'threshold_size';
}
