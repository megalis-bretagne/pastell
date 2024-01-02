<?php

declare(strict_types=1);

namespace Pastell\Client\IparapheurV5\Model;

enum SignificantPropertyType : string
{
    case TYPE = 'i_Parapheur_reserved_type';
    case SUBTYPE = 'i_Parapheur_reserved_subtype';
    case MAIN_DOCUMENT = 'i_Parapheur_reserved_mainDocument';
}
