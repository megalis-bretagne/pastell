<?php

declare(strict_types=1);

namespace Pastell\Command\Module;

use Journal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:module:force-delete-module',
    description: 'Add a short description for your command',
)]
class ForceDeleteModuleCommand extends Command
{
    public function __construct(
        private readonly \DocumentEntite $documentEntite,
        private readonly \JobQueueSQL $jobQueueSQL,
        private readonly \FluxEntiteSQL $fluxEntiteSQL,
        private readonly \DocumentSQL $documentSQL,
        private readonly \DonneesFormulaireFactory $donneesFormulaireFactory,
        private readonly \Journal $journal,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
        ;
    }

    /**
     * @throws \NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $flux = $input->getArgument('module');

        $documents = $this->documentSQL->getAllByType($flux);
        if (!$documents) {
            $io->warning(sprintf("Il n'y a pas de document de type %s\n", $flux));
        } else {
            foreach ($documents as $document) {
                $id_d = $document['id_d'];
                $docEntityInformation = $this->documentEntite->getEntite($id_d);
                foreach ($docEntityInformation as $entite) {
                    $id_e = $entite['id_e'];

                    $io->note(sprintf('Entite: %s, document: %s\n', $id_e, $id_d));

                    $id_job = $this->jobQueueSQL->getJobIdForDocument($id_e, $id_d);
                    if ($id_job) {
                        $io->note(sprintf('Le job: %s sera supprimé pour ce document\n', $id_job));
                    }
                }
            }
            $io->note(sprintf("%s documents vont être supprimés !\n", count($documents)));
        }

        $associations = $this->fluxEntiteSQL->getAssociations($flux);
        if (!$associations) {
            $io->warning(sprintf("Il n'y a pas d'association de type %s\n", $flux));

            if (!$documents) {
                return Command::INVALID;
            }
        }
        $associationPerEntity = [];
        foreach ($associations as $association) {
            if (isset($associationPerEntity[$association['id_e']])) {
                $associationPerEntity[$association['id_e']] ++;
            } else {
                $associationPerEntity[$association['id_e']] = 1;
            }
        }
        foreach ($associationPerEntity as $id_e => $value) {
            $io->note(sprintf(
                "%s association de flux vont être supprimés pour l'entité %s\n",
                $value,
                $id_e,
            ));
        }

        $answer = $io->ask('Etes-vous sur (o/N) ?');
        if ($answer != 'o') {
            $io->note("Aucun élément n'a été supprimé");
            return Command::SUCCESS;
        }

        foreach ($documents as $document) {
            $id_d = $document['id_d'];
            $docEntityInformation = $this->documentEntite->getEntite($id_d);
            $id_job = $this->jobQueueSQL->getJobIdForDocument($docEntityInformation[0]['id_e'], $id_d);
            if ($id_job) {
                $this->jobQueueSQL->deleteJob($id_job);
            }

            $info = $this->documentSQL->getInfo($id_d);
            $this->donneesFormulaireFactory->get($id_d)->delete();
            $this->documentSQL->delete($id_d);

            $message = sprintf(
                'Le document « %s » (%s) a été supprimé par un administrateur',
                $info['titre'],
                $id_d
            );
            $this->journal->add(Journal::DOCUMENT_ACTION, 0, $id_d, 'suppression', $message);
        }

        foreach ($associations as $association) {
            $this->fluxEntiteSQL->removeConnecteur($association['id_fe']);
        }

        $io->success('Les éléments ont été supprimés');
        return Command::SUCCESS;
    }
}
