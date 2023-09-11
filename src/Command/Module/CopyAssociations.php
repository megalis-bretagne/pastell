<?php

namespace Pastell\Command\Module;

use Exception;
use FluxEntiteSQL;
use Pastell\Command\BaseCommand;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CopyAssociations extends BaseCommand
{
    /**
     * @var ConnecteurAssociationService
     */
    private $connecteurAssociationService;
    /**
     * @var FluxEntiteSQL
     */
    private $fluxEntiteSQL;

    public function __construct(ConnecteurAssociationService $connecteurAssociationService, FluxEntiteSQL $fluxEntiteSQL)
    {
        $this->connecteurAssociationService = $connecteurAssociationService;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:module:copy-associations')
            ->setDescription('Copy the associations of the source module to the target module.')
            ->addArgument('source', InputOption::VALUE_REQUIRED, 'The source module')
            ->addArgument('target', InputOption::VALUE_REQUIRED, 'The target module')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = $input->getArgument('source');
        $target = $input->getArgument('target');
        $this->getIO()->title("Start copying `$source` associations to `$target` associations");

        $associations = $this->fluxEntiteSQL->getAssociations($source);
        $numberOfAssociations = count($associations);
        if ($input->isInteractive()) {
            $question = "There are $numberOfAssociations associations to copy, do you want to continue ?";
            if (!$this->getIO()->confirm($question, false)) {
                return 0;
            }
        }

        $this->getIO()->progressStart($numberOfAssociations);

        foreach ($associations as $association) {
            $this->connecteurAssociationService->addConnecteurAssociation(
                $association['id_e'],
                $association['id_ce'],
                $association['type'],
                0,
                $target,
                $association['num_same_type']
            );
            $this->getIO()->progressAdvance();
        }
        $this->getIO()->progressFinish();
        $this->getIO()->success('Done');

        return 0;
    }
}
