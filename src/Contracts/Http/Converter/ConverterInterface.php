<?php

namespace Baka\Contracts\Http\Converter;

interface ConverterInterface
{
    /**
     * Convert a Request to a whatever syntax we specify.
     *
     * @return void
     */
    public function convert();
}
