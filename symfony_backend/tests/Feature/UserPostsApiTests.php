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
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
        ], $this->getUserAuthClient());

        self::assertArrayHasKey('id', $this->response);
        self::assertGreaterThan(0, $this->response);
        unset($this->response['id']);
        $this->assertResponse([
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    public function testShow()
    {
        $this->get('/api/posts/show/' . ResponseMessages::EXISTING_USER_POST_ID, $this->getUserAuthClient());
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => ResponseMessages::EXISTING_USER_POST_ID,
            'title' => ResponseMessages::EXISTING_USER_TITLE,
            'content' => ResponseMessages::EXISTING_USER_CONTENT,
            'createdAt' => ResponseMessages::USER_POST_CREATED_AT,
            'updatedAt' => ResponseMessages::USER_POST_UPDATED_AT,
        ]);
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
            'updatedAt' => ResponseMessages::USER_POST_UPDATED_AT,
        ], $this->getUserAuthClient());
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => ResponseMessages::EXISTING_USER_POST_ID,
            'title' => ResponseMessages::NEW_USER_POST_TITLE,
            'content' => ResponseMessages::NEW_USER_POST_CONTENT,
            'createdAt' => ResponseMessages::USER_POST_CREATED_AT,
            'updatedAt' => Carbon::now(),
        ]);
    }

    public function testUpdateIfShortTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::USER_POST_SHORT_TITLE,
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
            'content' => ResponseMessages::USER_POST_SHORT_CONTENT,
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