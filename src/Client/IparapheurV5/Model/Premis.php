<?php

declare(strict_types=1);

namespace Pastell\Client\IparapheurV5\Model;

final class Premis
{
    /** @var PremisObject[] */
    public array $object;

    /** @var Event[]  */
    public array $event;

    /** @var Agent[] */
    public array $agent;
}
