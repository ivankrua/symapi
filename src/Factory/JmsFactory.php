<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Yevhenii Yolkin <e.v.yolkin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Factory;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

/**
 * Class JmsFactory
 */
class JmsFactory
{
    /**
     * Create api serializer.
     */
    public static function create(): Serializer
    {
        return SerializerBuilder::create()
            ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
            ->build();
    }
}
