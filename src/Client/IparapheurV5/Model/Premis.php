<?php

namespace Pastell\Client\IparapheurV5\Model;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Premis
{
    /** @var PremisObject[] */
    public array $object;

    /** @var Event[]  */
    public array $event;

    /** @var Agent[] */
    public array $agent;
}
