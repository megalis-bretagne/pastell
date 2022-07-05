<?php

namespace Pastell\Step\SAE\Enum;

enum SAEActionsEnum: string
{
    case GENERATE_SIP = 'generate-sip';
    case GENERATE_SIP_ERROR = 'generate-sip-error';
    case SEND_ARCHIVE = 'send-archive';
    case SEND_ARCHIVE_ERROR = 'erreur-envoie-sae';
    case CHECK_SAE = 'verif-sae';
    case CHECK_SAE_ERROR = 'verif-sae-erreur';
    case ACK_RECEIVED = 'ar-recu-sae';
    case VALIDATE_SAE = 'validation-sae';
    case VALIDATE_SAE_ERROR = 'validation-sae-erreur';
    case ARCHIVE_ACCEPTED = 'accepter-sae';
    case ARCHIVE_REJECTED = 'rejet-sae';
}
