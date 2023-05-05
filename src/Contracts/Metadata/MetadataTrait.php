<?php

declare(strict_types=1);

namespace Baka\Contracts\Metadata;

trait MetadataTrait
{
    /**
     * Key/Value pairs for the metadata.
     *
     * @var array
     */
    protected array $metadata = [];

    /**
     * Get metadata from the current object by it's key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set metadata for the current object.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setMetadata(string $key, $value) : void
    {
        $this->metadata[$key] = $value;
    }
}
