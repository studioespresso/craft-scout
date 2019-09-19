<?php

use League\Fractal\TransformerAbstract;

class FractalMockTransformers extends TransformerAbstract
{
    public function transform($entry)
    {
        return ['title' =>  $entry['title']];
    }
}
