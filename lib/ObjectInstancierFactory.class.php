<?php

class ObjectInstancierFactory
{
    /** @var  ObjectInstancier */
    private static $objetInstancier;

    public static function setObjectInstancier(ObjectInstancier $objectInstancier)
    {
        self::$objetInstancier = $objectInstancier;
    }

    /**
     *
     * Dieu tuera un chaton chaque fois que vous utiliserez cette fonction.
     *
     * @return ObjectInstancier
     */
    public static function getObjetInstancier()
    {
        return self::$objetInstancier;
    }
}
