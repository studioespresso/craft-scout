<?php

use League\Fractal\TransformerAbstract;

class BlogTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'bookCategories',
    ];

    public function transform($book)
    {
        return [
            'id'    =>  $book['id'],
            'title' =>  $book['title'],
        ];
    }

    /**
     * @return \League\Fractal\Resource\Item
     */
    public function includeBookCategories()
    {
        $categories = [[
            'id'    => 1,
            'title' => 'foo',
        ], [
            'id'    => 2,
            'title' => 'bar',
        ]];
        return $this->collection($categories, new BooksCategoriesTransformer);
    }

}

class BooksCategoriesTransformer extends TransformerAbstract
{
    public function transform($category)
    {
        return [
            'title' => $category['title'],
        ];
    }
}
