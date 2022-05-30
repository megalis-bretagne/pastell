<?php

require __DIR__ . "/../../vendor/autoload.php";

use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Dumper\StateMachineGraphvizDumper;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

$definitionBuilder = new DefinitionBuilder();
$definition = $definitionBuilder->addPlaces(['created', 'ready','locked', 'run', 'terminated','killed', 'suspended', 'error'])
    // Transitions are defined with a unique name, an origin place and a destination place
    ->addTransition(new Transition('ready', 'created', 'ready'))
    ->addTransition(new Transition('lock', 'ready', 'locked'))
    ->addTransition(new Transition('unlock', 'locked', 'run'))
    ->addTransition(new Transition('end', 'run', 'terminated'))
    ->addTransition(new Transition('retry', 'run', 'ready'))
    ->addTransition(new Transition('kill', ['run', 'locked'], 'killed'))
    ->addTransition(new Transition('retry', 'killed', 'ready'))
    ->addTransition(new Transition('suspend', 'ready', 'suspended'))
    ->addTransition(new Transition('to_error', 'run', 'error'))
    ->addTransition(new Transition('retry', 'suspended', 'ready'))
    ->addTransition(new Transition('retry', 'error', 'ready'))
    ->addTransition(new Transition('cancel', ['error', 'suspended', 'ready', 'killed'], 'terminated'))
    ->build()
;

$singleState = true; // true if the subject can be in only one state at a given time
$property = 'currentState'; // subject property name where the state is stored
$marking = new MethodMarkingStore($singleState, $property);
$workflow = new StateMachine($definition, $marking);

$graphvizDumper = new StateMachineGraphvizDumper();
echo $graphvizDumper->dump($definition);
