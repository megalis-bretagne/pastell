<?php

class ObjectInstancier
{
    /** @var array  */
    private $objects;

    public function __construct()
    {
        $this->objects = ['ObjectInstancier' => $this];
    }

    public function getInstance($class_name)
    {
        if (! isset($this->objects[$class_name])) {
            $this->objects[$class_name] =  $this->newInstance($class_name);
        }
        return $this->objects[$class_name];
    }

    public function setInstance($class_name, $value)
    {
        $this->objects[$class_name] = $value;
    }

    public function newInstance($className)
    {
        try {
            $reflexionClass = new ReflectionClass($className);
            if (! $reflexionClass->hasMethod('__construct')) {
                return $reflexionClass->newInstance();
            }
            $constructor = $reflexionClass->getMethod('__construct');
            $allParameters = $constructor->getParameters();
            $param = $this->bindParameters($allParameters, $className);
            return $reflexionClass->newInstanceArgs($param);
        } catch (Exception $e) {
            throw new Exception("En essayant d'inclure $className : {$e->getMessage()}", 0, $e);
        }
    }

    private function bindParameters(array $allParameters, $className)
    {
        $param = [];
        /** @var ReflectionParameter $parameters */
        foreach ($allParameters as $parameters) {
            /** @var ReflectionNamedType|ReflectionUnionType|null $type */
            $type = $parameters->getType();
            if ($type && !$type->isBuiltin()) {
                $class = new ReflectionClass($type->getName());
                $param_name = $class->getName();
            } else {
                $param_name = $parameters->getName();
            }

            $bind_value = null;
            try {
                $bind_value = $this->getInstance($param_name);
            } catch (Exception $e) {
                // Do nothing, parameter doesn't exist
                // If the parameter is optional, we return the default value
                // Otherwise, another exception is thrown below
            }
            if (! isset($bind_value)) {
                if ($parameters->isOptional()) {
                    $bind_value = $parameters->getDefaultValue();
                } else {
                    throw new UnrecoverableException("Impossible d'instancier $className car le paramètre {$parameters->name} est manquant");
                }
            }
            $param[] = $bind_value;
        }
        return $param;
    }
}
