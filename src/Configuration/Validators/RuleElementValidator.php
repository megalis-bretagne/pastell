<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\ActionElement;
use Pastell\Configuration\ModuleElement;
use Pastell\Configuration\RuleElement;

class RuleElementValidator implements ValidatorInterface
{
    private array $errors = [];

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allAction = $typeDefinition[ModuleElement::ACTION->value];
        foreach ($allAction as $key => $action) {
            if (is_array($action) && isset($action[ActionElement::RULE->value])) {
                $this->checkAllRule($action[ActionElement::RULE->value], $key . ':' . ActionElement::RULE->value);
            }
        }
        if (count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    private function checkAllRule(array $ruleList, $path): void
    {
        foreach ($ruleList as $rulekey => $rulevalue) {
            if (
                str_contains($rulekey, RuleElement::NO->value)
                || str_contains($rulekey, RuleElement::AND->value)
                || str_contains($rulekey, RuleElement::OR->value)
            ) {
                $this->checkAllRule($rulevalue, $path . ':' . $rulekey);
            }
            if (
                !in_array($rulekey, array_column(RuleElement::cases(), 'value'))
                && !(
                    str_contains($rulekey, RuleElement::NO->value)
                    || str_contains($rulekey, RuleElement::AND->value)
                    || str_contains($rulekey, RuleElement::OR->value)
                    )
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
