<?php

namespace Pastell\Command\Connector;

use Pastell\Command\BaseCommand;
use Pastell\Service\Droit\DroitService;
use RoleDroit;
use RoleSQL;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddConnectorPermission extends BaseCommand
{
    /**
     * @var RoleSQL
     */
    private $roleSQL;

    /**
     * @var RoleDroit
     */
    private $roleDroit;

    public function __construct(
        RoleSQL $roleSQL,
        RoleDroit $roleDroit
    ) {
        $this->roleSQL = $roleSQL;
        $this->roleDroit = $roleDroit;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:connector:add-connector-permission')
            ->setDescription(
                'If there is no connecteur:lecture or connecteur:edition permission, we copy those from entite'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getIO()->title(
            "Start If there is no connecteur:lecture or connecteur:edition permission, we copy those from entite"
        );

        $roleDroitConnecteur = [];
        $roleDroitEntite = [];
        foreach ($this->roleSQL->getAllRole() as $role) {
            $droit = $this->roleSQL->getDroit($this->roleDroit->getAllDroit(), $role['role']);

            $droitConnecteur = array_filter($droit, function ($value, $key) {
                list($part) = explode(":", $key);
                return $value == 1 && $part == DroitService::DROIT_CONNECTEUR;
            }, ARRAY_FILTER_USE_BOTH);
            if ($droitConnecteur) {
                $roleDroitConnecteur[$role['role']] = array_keys($droitConnecteur);
            }

            $droitEntite = array_filter($droit, function ($value, $key) {
                list($part) = explode(":", $key);
                return $value == 1 && $part == DroitService::DROIT_ENTITE;
            }, ARRAY_FILTER_USE_BOTH);
            if ($droitEntite) {
                $roleDroitEntite[$role['role']] = array_keys($droitEntite);
            }
        }

        if (count($roleDroitConnecteur)) {
            $this->getIO()->comment(
                "Nothing to do. There are already connector permission for role: " . json_encode($roleDroitConnecteur)
            );
            return 0;
        }

        $numberOfRole = count($roleDroitEntite);
        if ($input->isInteractive()) {
            $question = "There are $numberOfRole role whith entite permission, do you want to copy them to connector ?";
            if (!$this->getIO()->confirm($question, false)) {
                return 0;
            }
        }

        $this->getIO()->progressStart($numberOfRole);
        foreach ($roleDroitEntite as $role => $droitEntite) {
            $this->getIO()->newLine();
            foreach ($droitEntite as $droit) {
                list(, $type) = explode(":", $droit);
                $this->roleSQL->addDroit($role, DroitService::DROIT_CONNECTEUR . ":" . $type);
                $this->getIO()->writeln('Add ' . DroitService::DROIT_CONNECTEUR . ":" . $type . ' for ' . $role);
            }
            $this->getIO()->progressAdvance();
        }
        $this->getIO()->progressFinish();
        $this->getIO()->success('Success for ' . $numberOfRole . ' role.');
        return 0;
    }
}
