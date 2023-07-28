<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum DisplayedField: string
{
    case DATE_DERNIER_ETAT = 'date_dernier_etat';
    case DERNIER_ETAT = 'dernier_etat';
    case ENTITE = 'entite';
    case TITRE = 'titre';
}
