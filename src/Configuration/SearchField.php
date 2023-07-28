<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum SearchField: string
{
    case ETAT_TRANSIT = 'etatTransit';
    case ID_E = 'id_e';
    case LASTETAT = 'lastetat';
    case LAST_STATE_BEGIN = 'last_state_begin';
    case NO_ETAT_TRANSIT = 'notEtatTransit';
    case SEARCH = 'search';
    case STATE_BEGIN = 'state_begin';
    case TRI = 'tri';
    case TYPE = 'type';
}
