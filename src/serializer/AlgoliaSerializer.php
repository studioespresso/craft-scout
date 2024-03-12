<?php

namespace rias\scout\serializer;

class AlgoliaSerializer extends \League\Fractal\Serializer\ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param ?string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection(?string $resourceKey, array $data): array
    {
        if ($resourceKey) {
            return [$resourceKey => $data];
        }

        return $data;
    }
}
