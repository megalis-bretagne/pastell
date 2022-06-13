<?php

namespace Pastell\Step\SAE\Enum;

enum SAEFieldsEnum: string
{
    case SAE_CONFIG = 'sae_config';
    case SAE_SHOW = 'sae_show';
    case JOURNAL = 'journal';
    case JOURNAL_START_DATE = 'date_journal_debut';
    case JOURNAL_END_DATE = 'date_cloture_journal';
    case JOURNAL_END_DATE_ISO8601 = 'date_cloture_journal_iso8601';
    case SAE_TRANSFERT_ID = 'sae_transfert_id';
    case SAE_BORDEREAU = 'sae_bordereau';
    case SAE_ARCHIVE = 'sae_archive';
    case AR_SAE = 'ar_sae';
    case SAE_ACK_COMMENT = 'sae_ack_comment';
    case REPLY_SAE = 'reply_sae';
    case SAE_ARCHIVAL_IDENTIFIER = 'sae_archival_identifier';
    case SAE_ATR_COMMENT = 'sae_atr_comment';
}
