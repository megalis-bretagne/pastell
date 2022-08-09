<?php

declare(strict_types=1);

namespace Pastell\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use UnrecoverableException;

class ConnectorValidation
{
    public function __construct(
        private readonly ConnectorConfiguration $connectorConfiguration
    ) {
    }

    /**
     * @throws UnrecoverableException
     */
    public function getConfiguration(string $filePath): array
    {
        $config = Yaml::parseFile($filePath);
        $configuration = (new Processor())->processConfiguration(
            $this->connectorConfiguration,
            [$config]
        );
        $this->checkChoiceAction($configuration);

        return $configuration;
    }

    /**
     * @throws UnrecoverableException
     */
    private function checkChoiceAction(array $configuration): void
    {
        $actionIdList = $this->getActionIdList($configuration);
        if (empty($configuration[ConnectorConfiguration::FORMULAIRE])) {
            return;
        }

        foreach ($configuration[ConnectorConfiguration::FORMULAIRE] as $pageProperties) {
            foreach ($pageProperties as $elementId => $elementProperties) {
                if (empty($elementProperties[ConnectorConfiguration::ELEMENT_CHOICE_ACTION])) {
                    continue;
                }
                if (!in_array($elementProperties[ConnectorConfiguration::ELEMENT_CHOICE_ACTION], $actionIdList, true)) {
                    throw new UnrecoverableException(sprintf(
                        "L'action de choix %s défini pour l'élément %s n'existe pas dans les actions",
                        $elementProperties[ConnectorConfiguration::ELEMENT_CHOICE_ACTION],
                        $elementId,
                    ));
                }
            }
        }
    }

    private function getActionIdList(array $configuration): array
    {
        if (empty($configuration[ConnectorConfiguration::ACTION])) {
            return [];
        }
        return array_keys($configuration[ConnectorConfiguration::ACTION]);
    }
}
