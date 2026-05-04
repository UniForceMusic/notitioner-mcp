<?php

namespace Src\Toolboxes;

use Sentience\Database\Databases\DatabaseInterface;

abstract class Toolbox
{
    public function __construct(protected DatabaseInterface $database)
    {
    }
}
