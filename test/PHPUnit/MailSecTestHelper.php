<?php

declare(strict_types=1);

final class MailSecTestHelper extends PastellTestCase
{
    use MailsecTestTrait;

    public const FLUX_MAILSEC_BIDIR = 'mailsec-bidir';
    public const ACTION_MAILSEC_BIDIR_ENVOI_MAIL = 'envoi-mail';

    public const FLUX_MAILSEC = 'mailsec';
    public const ACTION_MAILSEC_ENVOI_MAIL = 'envoi';
}
