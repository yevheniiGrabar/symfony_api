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
        ], $this->getUserAuthClient());

        self::assertResponseOk();
        self::assertArrayHasKey('id', $this->response);
        self::assertArrayHasKey('title', $this->response);
        self::assertArrayHasKey('content', $this->response);
        self::assertArrayHasKey('createdAt', $this->response);
        self::assertArrayHasKey('updatedAt', $this->response);
        self::assertGreaterThan(0, $this->response['id']);

        $createdAtFromResponse = Carbon::parse($this->response['createdAt']);
        $createdAt = $createdAtFromResponse->format('Y-m-d');
        $updatedAtFromResponse = Carbon::parse($this->response['updatedAt']);
        $updatedAt = $updatedAtFromResponse->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        self::assertEquals(ResponseMessages::NEW_USER_POST_TITLE, $this->response['title']);
        self::assertEquals(ResponseMessages::NEW_USER_POST_CONTENT, $this->response['content']);
        self::assertEquals($currentDate, $createdAt);
        self::assertEquals($currentDate, $updatedAt);
    }

    public function testStoreIfShortTitle()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::DEFAULT_POST_SHORT_TITLE,
            'content' => ResponseMessages::NEW_USER_POST_CONTENT,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testStoreIfShortContent()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testStoreWithoutTitle()
    {
        $this->post('/api/posts/store', [
            'title' => '',
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testStoreWithoutContent()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => '',
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testShow()
    {
        $this->get('/api/posts/show/' . ResponseMessages::EXISTING_USER_POST_ID, $this->getUserAuthClient());

        self::assertResponseOk();
        self::assertArrayHasKey('id', $this->response);
        self::assertArrayHasKey('title', $this->response);
        self::assertArrayHasKey('content', $this->response);
        self::assertArrayHasKey('createdAt', $this->response);
        self::assertArrayHasKey('updatedAt', $this->response);

        $createdAtFromResponse = Carbon::parse($this->response['createdAt']);
        $updatedAtFromResponse = Carbon::parse($this->response['updatedAt']);
        $createdAt = $createdAtFromResponse->format('Y-m-d');
        $updatedAt = $updatedAtFromResponse->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        self::assertEquals(ResponseMessages::EXISTING_USER_POST_ID, $this->response['id']);
        self::assertEquals(ResponseMessages::EXISTING_USER_TITLE, $this->response['title']);
        self::assertEquals(ResponseMessages::EXISTING_USER_CONTENT, $this->response['content']);
        self::assertEquals($currentDate, $createdAt);
        self::assertEquals($currentDate, $updatedAt);
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
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => ResponseMessages::NEW_USER_POST_CONTENT,
        ], $this->getUserAuthClient());

        self::assertResponseOk();
        self::assertArrayHasKey('id', $this->response);
        self::assertArrayHasKey('title', $this->response);
        self::assertArrayHasKey('content', $this->response);
        self::assertArrayHasKey('createdAt', $this->response);
        self::assertArrayHasKey('updatedAt', $this->response);

        $createdAtFromResponse = Carbon::parse($this->response['createdAt']);
        $updatedAtFromResponse = Carbon::parse($this->response['updatedAt']);
        $createdAt = $createdAtFromResponse->format('Y-m-d');
        $updatedAt = $updatedAtFromResponse->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        self::assertEquals(ResponseMessages::EXISTING_USER_POST_ID, $this->response['id']);
        self::assertEquals(ResponseMessages::NEW_USER_POST_TITLE, $this->response['title']);
        self::assertEquals(ResponseMessages::NEW_USER_POST_CONTENT, $this->response['content']);
        self::assertEquals($currentDate, $createdAt);
        self::assertEquals($currentDate, $updatedAt);
    }

    public function testUpdateIfShortTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::DEFAULT_POST_SHORT_TITLE,
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateIfShortContent()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => '',
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutContent()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => '',
            'updatedAt' => Carbon::now(),
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateAnotherPost()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }

    public function testDelete()
    {
        $this->delete('/api/posts/delete/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        self::assertResponseOk();
    }

    public function testDeleteAnotherPost()
    {
        $this->delete('/api/posts/delete/' . ResponseMessages::EXISTING_ADMIN_POST_ID, $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }
}
