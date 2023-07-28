<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\ActionElement;
use Pastell\Configuration\ModuleElement;
use Pastell\Configuration\RuleElement;

class RuleElementValidator implements ValidatorInterface
{
    private array $errors;

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allAction = $typeDefinition[ModuleElement::ACTION->value];
        foreach ($allAction as $key => $action) {
            if (is_array($action) && isset($action[ActionElement::RULE->value])) {
                $this->checkAllRule($action[ActionElement::RULE->value], $key . ':' . ActionElement::RULE->value);
            }
        }
        return count($this->errors) === 0;
    }

    private function checkAllRule(array $ruleList, $path): void
    {
        foreach ($ruleList as $rulekey => $rulevalue) {
            $containsNoAndOr = str_starts_with($rulekey, RuleElement::NO->value)
                || str_starts_with($rulekey, RuleElement::AND->value)
                || str_starts_with($rulekey, RuleElement::OR->value);
            if ($containsNoAndOr) {
                $this->checkAllRule($rulevalue, $path . ':' . $rulekey);
            }
            if (
                !in_array($rulekey, array_column(RuleElement::cases(), 'value'))
                && !($containsNoAndOr)
            ) {
                $this->errors[] = "<b>$path</b>: la clé <b>$rulekey</b> n'est pas attendu";
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
