<?php

namespace Baka\Contracts\Database;

interface ElasticModelInterface
{
    public function getSource() : string;

    public function getRelations() : array;
}
