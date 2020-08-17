<?php


namespace Tests\Feature\Http\Controllers\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;
use Illuminate\Support\Arr;
use Tests\Traits\TestResources;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidation, TestSaves, TestResources;
    private $serializeFields = [
        "id",
        "title",
        "description",
        "year_launched",
        "opened",
        "rating",
        "duration",
        "video_file",
        "thumb_file",
        "banner_file",
        "trailer_file",
        "deleted_at",
        "created_at",
        "updated_at",
        "categories" => [
            '*' => [
                "id",
                "name",
                "description",  
                "is_active", 
                "created_at",
                "updated_at",
            ]
        ],
        "genres" => [
            "*" => [
                "id",
                "name",
                "description",
                "is_active",
                "deleted_at",
                "created_at",
                "updated_at"
            ]
        ]
    ];

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializeFields
                ],
                'links' => [],
                'meta' => []
            ]);
            $this->assertResource($response, VideoResource::collection(collect([$this->video])));
            $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'category_id' => '',
            'genre_id' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }
    public function testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = [
            'duration' => 's',
        ];

        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [
            'year_launched' =>  'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format'=>'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format'=>'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened' => 's',
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating' => 0
        ];

        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }



    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'category_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'category_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            "category_id" => [$category->id]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

    }

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genre_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'category_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            "genre_id" => [$genre->id]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testSave()
    {
        // $categoty = factory(Category::class)->create();
        // $genre = factory(Genre::class)->create();
        // $genre->categories()->sync($categoty->id);

        $testData = Arr::except($this->sendData, ['category_id', 'genre_id']);

        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $testData + ['opened' => false],
            ],
            [
                'send_data' => $this->sendData,                        
                'test_data' => $testData + ['opened' => true],
            ],
            [
                'send_data' => $this->sendData,
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]],
            ],
        ];

        foreach ($data as $key => $value) {

            $response = $this->assertStore(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                "data" => $this->serializeFields
            ]);
            // $this->assertHasCategory(
            //     $response->json('id'),
            //     $value['send_data']['category_id'][0]
            // );

            // $this->assertHasGenre(
            //     $response->json('id'),
            //     $value['send_data']['genre_id'][0]
            // );
            $response = $this->assertUpdate(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                "data" => $this->serializeFields
            ]);
            // $this->assertHasCategory(
            //     $response->json('id'),
            //     $value['send_data']['category_id'][0]
            // );

            // $this->assertHasGenre(
            //     $response->json('id'),
            //     $value['send_data']['genre_id'][0]
            // );
            $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
        }
    }
    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                "data" => $this->serializeFields
            ]);
        $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
        $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testDelete()
    {
        $video = factory(Video::class)->create();
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($video->id));
        $this->assertNotNull(Video::withTrashed()->find($video->id)); //verifica se consegue pegar a genre na lixera (exclusão logica)
    }

    protected  function assertHasCategory($videoId, $cateoryId)
    {
        $this->assertDatabaseHas('category_video', [
            "category_id" => $cateoryId,
            "video_id" => $videoId
        ]);
    }

    protected  function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            "genre_id" => $genreId,
            "video_id" => $videoId
        ]);
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }

}

