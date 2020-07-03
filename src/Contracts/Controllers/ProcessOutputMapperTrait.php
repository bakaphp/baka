<?php

declare(strict_types=1);

namespace Baka\Contracts\Controllers;

use AutoMapperPlus\DataType;
use Baka\Http\Exception\InternalServerErrorException;
use StdClass;

trait ProcessOutputMapperTrait
{
    protected $dto = null;
    protected $dtoMapper = null;

    /**
     * Format Controller Result base on a Mapper.
     *
     * @param mixed $results
     *
     * @return void
     */
    protected function processOutput($results)
    {
        $this->canUseMapper();

        $mapMultiple = false;
        $mapperModel = get_class($this->model);

        //if its simple, pagination or relationship we need to map array to sdtclass
        if (
            (is_array($results) || is_iterable($results)) &&
            (($this->request->withPagination() && $this->request->withRelationships()) ||
            (!$this->request->withPagination() && $this->request->withRelationships()))
        ) {
            $mapperModel = DataType::ARRAY;
            $this->dto = StdClass::class;
            $mapMultiple = true;
        }

        $this->dtoConfig->registerMapping($mapperModel, $this->dto)
            ->useCustomMapper($this->dtoMapper);

        if ($this->request->withPagination()) {
            $results['data'] = $this->mapper->mapMultiple(
                $results['data'],
                $this->dto,
                $this->getMapperOptions()
            );
            return  $results;
        }

        if ($mapMultiple || is_iterable($results)) {
            return $this->mapper->mapMultiple(
                $results,
                $this->dto,
                $this->getMapperOptions()
            );
        } else {
            return $this->mapper->map(
                $results,
                $this->dto,
                $this->getMapperOptions()
            );
        }
    }

    /**
     * Can we use the mapper on this request?
     *
     * @return boolean
     */
    protected function canUseMapper() : bool
    {
        if (!is_object($this->model) || empty($this->dto)) {
            throw new InternalServerErrorException('No Mapper configured on this controller ' . get_class($this));
        }

        return true;
    }

    /**
     * If we have relationships send them as additional context to the mapper.
     *
     * @return array
     */
    protected function getMapperOptions() : array
    {
        if ($this->request->hasQuery('relationships')) {
            return [
                'relationships' => explode(',', $this->request->getQuery('relationships'))
            ];
        }

        return [];
    }
}
