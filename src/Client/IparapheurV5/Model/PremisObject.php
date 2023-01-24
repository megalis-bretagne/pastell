<?php

namespace Pastell\Client\IparapheurV5\Model;

use Symfony\Component\Serializer\Annotation\SerializedName;

class PremisObject
{
    #[SerializedName('@xsi:type')]
    public Type $type;

    public string $originalName;

    public ObjectIdentifier $objectIdentifier;

    public PreservationLevel $preservationLevel;
}
