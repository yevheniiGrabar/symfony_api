<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
use App\Tests\TestCases\FeatureTestCase;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class UserPostsApiTests extends FeatureTestCase
{
    public function testStore()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => ResponseMessages::NEW_USER_POST_CONTENT,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ], $this->getUserAuthClient());

        $createdAtFromResponse = Carbon::parse($this->response['createdAt']);
        $createdAt = $createdAtFromResponse->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        self::assertArrayHasKey('id', $this->response);
        self::assertArrayHasKey('createdAt', $this->response);
        self::assertGreaterThan(0, $this->response);
        //unset($this->response['id']);
        self::assertEquals($createdAt, $currentDate);
    }

    public function testStoreIfShortTitle()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::DEFAULT_POST_SHORT_TITLE,
            'content' => ResponseMessages::NEW_USER_POST_CONTENT,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testStoreIfShortContent()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testStoreWithoutTitle()
    {
        $this->post('/api/posts/store', [
            'title' => '',
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testStoreWithoutContent()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => '',
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testShow()
    {
        $this->get('/api/posts/show/' . ResponseMessages::EXISTING_USER_POST_ID, $this->getUserAuthClient());
        $this->assertResponseOk();
        $createdAtFromResponse = Carbon::parse($this->response['createdAt']);
        $createdAt = $createdAtFromResponse->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');
        self::assertArrayHasKey('createdAt', $this->response);
        self::assertEquals($currentDate, $createdAt);
    }

    public function testShowAnotherPost()
    {
        $this->getUserAuthClient();
        $this->get('/api/posts/show/' . ResponseMessages::EXISTING_ADMIN_POST_ID, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }

    public function testUpdate()
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => ResponseMessages::NEW_USER_POST_CONTENT,
            'updatedAt' => $currentDate
        ], $this->getUserAuthClient());
        $this->assertResponseOk();
        self::assertArrayHasKey('updatedAt', $this->response);
        $formattedDate = Carbon::parse($this->response['updatedAt']);
        $updatedAt = $formattedDate->format('Y-m-d');
        self::assertEquals($currentDate, $updatedAt);
    }

    public function testUpdateIfShortTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::DEFAULT_POST_SHORT_TITLE,
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateIfShortContent()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => '',
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::TITLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutContent()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => '',
            'updatedAt' => Carbon::now(),
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateAnotherPost()
    {
        $this->getUserAuthClient();
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }

    public function testDelete()
    {
        $this->delete('/api/posts/delete/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        $this->assertResponseOk();
    }

    public function testDeleteAnotherPost()
    {
        $this->getUserAuthClient();
        $this->delete('/api/posts/delete/' . ResponseMessages::EXISTING_ADMIN_POST_ID,);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }
}