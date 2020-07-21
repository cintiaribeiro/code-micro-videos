<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\Exception\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidation;

class VideoControllerTest extends TestCase
{
   // use DatabaseMigrations, TestSaves, TestValidation, TestUploads;

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $this->sendData = [
            'title' => 'Titulo',
            'description' => "Descrição",
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];
    }















//    public function testRollbackStore()
//    {
//        //configuro o mockery
//        $controller = \Mockery::mock(VideoController::class)
//            ->makePartial()
//            ->shouldAllowMockingProtectedMethods();
//
//        //ignoro a validação dos dados passados
//        $controller->shouldReceive('validate')
//            ->withAnyArgs()
//            ->andReturn($this->sendData);
//
//        $controller->shouldReceive('rolesStore')
//            ->withAnyArgs()
//            ->andReturn([]);
//
//        //forçando o método retornar um exception
//        $controller->shouldReceive('handleRelations')
//            ->once()
//            ->andThrow(new TestException());
//
//        $request = \Mockery::mock(Request::class);
//        $request->shouldReceive('get')
//                ->withAnyArgs()
//                ->andReturnNull();
//
//        $hasError = false;
//        try {
//            $controller->store($request);
//        } catch (TestException $e) {
//            $this->assertCount(1, Video::all());
//            $hasError = true;
//        }
//
//        $this->assertTrue($hasError);
//    }
//
//    public function testRollbackUpdate()
//    {
//        //configuro o mockery
//        $controller = \Mockery::mock(VideoController::class)
//            ->makePartial()
//            ->shouldAllowMockingProtectedMethods();
//
//        //ignoro a validação dos dados passados
//        $controller->shouldReceive('findOrFail')
//            ->withAnyArgs()
//            ->andReturn($this->video);
//
//        //ignoro a validação dos dados passados
//        $controller->shouldReceive('validate')
//            ->withAnyArgs()
//            ->andReturn([
//                "name"=>'testt'
//            ]);
//
//        $controller->shouldReceive('rolesUpdate')
//            ->withAnyArgs()
//            ->andReturn([]);
//
//        //forçando o método retornar um exception
//        $controller->shouldReceive('handleRelations')
//            ->once()
//            ->andThrow(new TestException());
//
//        $request = \Mockery::mock(Request::class);
//        $request->shouldReceive('get')
//            ->withAnyArgs()
//            ->andReturnNull();
//
//        $hasError = false;
//        try {
//            $controller->update($request, 1);
//        } catch (TestException $e) {
//            $this->assertCount(1, Video::all());
//            $hasError = true;
//        }
//
//        $this->assertTrue($hasError);
//    }



//    public function testSyncCategories()
//    {
//        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
//        $genre = factory(Genre::class)->create();
//        $genre->categories()->sync($categoriesId);
//        $genreId = $genre->id;
//
//        $response = $this->json(
//            'POST',
//            $this->routeStore(),
//            $this->sendData + [
//                'genre_id' => [$genreId],
//                'category_id' => [$categoriesId[0]]
//            ]
//        );
//
//        $this->assertDatabaseHas('category_video', [
//           'category_id' => $categoriesId[0],
//           'video_id' =>  $response->json('id')
//        ]);
//
//        $response = $this->json(
//            'PUT',
//            route('videos.update', ['video' => $response->json('id')]),
//            $this->sendData + [
//                'genre_id' => [$genreId],
//                'category_id' => [$categoriesId[1], $categoriesId[2]]
//            ]
//        );
//
//        $this->assertDatabaseMissing('category_video',[
//            'video_id' => $response->json('id'),
//            'category_id' => $categoriesId[0]
//        ]);
//
//        $this->assertDatabaseHas('category_video', [
//            'category_id' => $categoriesId[1],
//            'video_id' =>  $response->json('id')
//        ]);
//
//        $this->assertDatabaseHas('category_video', [
//            'category_id' => $categoriesId[2],
//            'video_id' =>  $response->json('id')
//        ]);
//    }
//
//    public function testSyncGenres()
//    {
//        /** @var Collection $genres **/
//        $genres = factory(Genre::class, 3)->create();
//        $genresId = $genres->pluck('id')->toArray();
//        $categotyId = factory(Category::class)->create()->id;
//        $genres->each(function ($genre) use ($categotyId){
//           $genre->categories()->sync($categotyId);
//        });
//
//        $response = $this->json(
//            'POST',
//            $this->routeStore(),
//            $this->sendData +
//            [
//                'category_id' => [$categotyId],
//                'genre_id' => [$genresId[0]]
//            ]
//        );
//
//        $this->assertDatabaseHas('genre_video',[
//                'video_id' => $response->json('id'),
//                'genre_id' => $genresId[0]
//            ]
//        );
//
//        $response = $this->json(
//            'PUT',
//           route('videos.update', ['video' => $response->json('id')]),
//            $this->sendData + [
//                'category_id' => [$categotyId],
//                'genre_id' => [$genresId[1], $genresId[2]]
//            ]
//        );
//
//        $this->assertDatabaseMissing('genre_video', [
//            'video_id' => $response->json('id'),
//            'genre_id' => $genresId[0]
//        ]);
//
//        $this->assertDatabaseHas('genre_video', [
//            'video_id' => $response->json('id'),
//            'genre_id' => $genresId[1]
//        ]);
//
//        $this->assertDatabaseHas('genre_video', [
//            'video_id' => $response->json('id'),
//            'genre_id' => $genresId[2]
//        ]);
//
//    }


}
