<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use FakeEngine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;

class AbstractEngineTest extends Unit
{
    protected function _before()
    {
        parent::_before();

        $scout = new Scout('scout');
        $scout->setSettings([
            'engine' => FakeEngine::class,
            'indices' => [
                ScoutIndex::create('blog')
                    ->splitElementsOn([
                        'body',
                        'article',
                    ]),
            ],
        ]);
        $scout->init();
    }

    /** @test * */
    public function it_can_split_objects_on_attributes()
    {
        /** @var FakeEngine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()[0];

        $objects = [
            [
                'objectID' => 1,
                'title' => 'One',
                'body' => [
                    'Paragraph 1',
                    'Paragraph 2',
                    'Paragraph 3',
                ],
            ],
        ];

        $this->assertEqualsCanonicalizing([
            'save' => [
                ['objectID' => '1_0', 'title' => 'One', 'body' => 'Paragraph 1', 'distinctID' => 1],
                ['objectID' => '1_1', 'title' => 'One', 'body' => 'Paragraph 2', 'distinctID' => 1],
                ['objectID' => '1_2', 'title' => 'One', 'body' => 'Paragraph 3', 'distinctID' => 1],
            ],
            'delete' => [
                [
                    'objectID' => 1,
                    'title' => 'One',
                    'body' => [
                        'Paragraph 1',
                        'Paragraph 2',
                        'Paragraph 3',
                    ],
                ],
            ],
        ], $engine->splitObjects($objects));
    }

    /** @test * */
    public function it_handles_when_the_object_field_isnt_an_array()
    {
        /** @var FakeEngine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()[0];

        $objects = [
            [
                'objectID' => 1,
                'title' => 'One',
                'body' => 'Paragraph 1',
            ],
        ];

        $this->assertEqualsCanonicalizing([
            'save' => [
                ['objectID' => 1, 'title' => 'One', 'body' => 'Paragraph 1', 'distinctID' => 1],
            ],
            'delete' => [],
        ], $engine->splitObjects($objects));
    }

    /** @test * */
    public function it_handles_multiple_split_fields()
    {
        /** @var FakeEngine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()[0];

        $objects = [
            [
                'objectID' => 1,
                'title' => 'One',
                'body' => [
                    'Paragraph 1',
                    'Paragraph 2',
                    'Paragraph 3',
                ],
                'article' => [
                    'Paragraph 1',
                    'Paragraph 2',
                    'Paragraph 3',
                ],
            ],
        ];

        $this->assertEqualsCanonicalizing([
            'save' => [
                ['objectID' => '1_0', 'title' => 'One', 'body' => 'Paragraph 1', 'article' => 'Paragraph 1', 'distinctID' => 1],
                ['objectID' => '1_1', 'title' => 'One', 'body' => 'Paragraph 1', 'article' => 'Paragraph 2', 'distinctID' => 1],
                ['objectID' => '1_2', 'title' => 'One', 'body' => 'Paragraph 1', 'article' => 'Paragraph 3', 'distinctID' => 1],
                ['objectID' => '1_3', 'title' => 'One', 'body' => 'Paragraph 2', 'article' => 'Paragraph 1', 'distinctID' => 1],
                ['objectID' => '1_4', 'title' => 'One', 'body' => 'Paragraph 2', 'article' => 'Paragraph 2', 'distinctID' => 1],
                ['objectID' => '1_5', 'title' => 'One', 'body' => 'Paragraph 2', 'article' => 'Paragraph 3', 'distinctID' => 1],
                ['objectID' => '1_6', 'title' => 'One', 'body' => 'Paragraph 3', 'article' => 'Paragraph 1', 'distinctID' => 1],
                ['objectID' => '1_7', 'title' => 'One', 'body' => 'Paragraph 3', 'article' => 'Paragraph 2', 'distinctID' => 1],
                ['objectID' => '1_8', 'title' => 'One', 'body' => 'Paragraph 3', 'article' => 'Paragraph 3', 'distinctID' => 1],
            ],
            'delete' => [
                [
                    'objectID' => 1,
                    'title' => 'One',
                    'body' => [
                        'Paragraph 1',
                        'Paragraph 2',
                        'Paragraph 3',
                    ],
                    'article' => [
                        'Paragraph 1',
                        'Paragraph 2',
                        'Paragraph 3',
                    ],
                ],
            ],
        ], $engine->splitObjects($objects));
    }
}
