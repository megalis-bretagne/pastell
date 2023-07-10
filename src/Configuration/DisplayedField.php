<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum DisplayedField: string
{
    case TITRE = 'titre';
    case ENTITE = 'entite';
    case DERNIER_ETAT = 'dernier_etat';
    case DATE_DERNIER_ETAT = 'date_dernier_etat';

}
