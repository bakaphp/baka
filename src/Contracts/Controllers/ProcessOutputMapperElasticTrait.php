<?php

declare(strict_types=1);

namespace Baka\Contracts\Controllers;

use AutoMapperPlus\DataType;
use Phalcon\Mvc\Model\Resultset\Simple;
use StdClass;

trait ProcessOutputMapperElasticTrait
{
    use ProcessOutputMapperTrait;

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

        $mapperModel = get_class($this->model);

        //Phalcon 4 now returns resultset for empty results
        $isSimpleResponse = function ($results) {
            return is_object($results) && get_class($results) == Simple::class;
        };

        //if its simple, pagination or relationship we need to map array to StdClass
        if (
            (is_array($results) || is_iterable($results)) &&
            empty($results)
        ) {
            $mapperModel = DataType::ARRAY;
            $this->dto = StdClass::class;
        }
        $this->dtoConfig->registerMapping($mapperModel, $this->dto)
            ->useCustomMapper($this->dtoMapper);

        if ($this->request->withPagination()) {
            $results['data'] = $this->mapper->mapMultiple(
                $results['data'],
                $this->dto,
                $this->getMapperOptions()
            );
            return $results;
        }

        if (is_iterable($results) &&
            (
                is_array(current($results))
                || is_object(current($results))
                || $isSimpleResponse($results)
                || (is_array($results) && empty($results))
            )) {
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
}
