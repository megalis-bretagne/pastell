<?php

namespace Pastell\Command\Module;

use Exception;
use FluxControler;
use FluxEntiteSQL;
use Pastell\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CopyAssociations extends BaseCommand
{

    /**
     * @var FluxControler
     */
    private $fluxController;
    /**
     * @var FluxEntiteSQL
     */
    private $fluxEntiteSQL;

    public function __construct(FluxControler $fluxController, FluxEntiteSQL $fluxEntiteSQL)
    {
        $this->fluxController = $fluxController;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
        parent::__construct();
    }

    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getArgument('source');
        $target = $input->getArgument('target');
        $this->getIO()->title("Start copying `$source` associations to `$target` associations");
        $associations = $this->fluxEntiteSQL->getAssociations($source);
        $this->getIO()->progressStart(count($associations));

        foreach ($associations as $association) {
            $this->fluxController->editionModif(
                $association['id_e'],
                $target,
                $association['type'],
                $association['id_ce'],
                $association['num_same_type']
            );
            $this->getIO()->progressAdvance();
        }
        $this->getIO()->progressFinish();
        $this->getIO()->success('Done');

        return 0;
    }
}
