<?php

namespace Pastell\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailSecCopyFromToReplyTo extends BaseCommand
{
    /**
     * @var ConnecteurEntiteSQL
     */
    private $connecteurEntiteSql;

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSql
    ) {
        $this->connecteurEntiteSql = $connecteurEntiteSql;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:connector:mailsec-copy-from-to-replyto')
            ->setDescription(
                'For Mailsec connector: If there is no mailsec_reply_to, we copy it from mailsec_from'
            )
        ;
    }
}
