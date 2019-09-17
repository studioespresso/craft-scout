<?php

namespace rias\scout\serializer;

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
