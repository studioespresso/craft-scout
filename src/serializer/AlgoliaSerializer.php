<?php
namespace rias\scout\serializer;

use League\Fractal\Serializer\ArraySerializer;
use yii\helpers\VarDumper;

class AlgoliaSerializer extends \League\Fractal\Serializer\ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey === false) {
            return [$resourceKey => $data];
        }

        return $data;
    }
}