<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum RuleElement: string
{
    case CONTENT = 'content';
    case DOCUMENT_IS_VALID = 'document_is_valide';
    case DROIT_ID_U = 'droit_id_u';
    case HAS_ACTION = 'has-action';
    case LAST_ACTION = 'last-action';
    case NO_ACTION = 'no-action';
    case NO_LAST_ACTION = 'no-last-action';
    case ROLE_ID_E = 'role_id_e';
    case TYPE_ID_E = 'type_id_e';
    case AND = 'and_';
    case OR = 'or_';
    case NO = 'no_';
}
