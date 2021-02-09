<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
use App\Tests\TestCases\FeatureTestCase;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AdminPostsApiTests extends FeatureTestCase
{
    public function testStore()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::EXISTING_ADMIN_TITLE,
            'content' => ResponseMessages::EXISTING_ADMIN_CONTENT,
        ], $this->getAdminAuthClient());

        self::assertArrayHasKey('id', $this->response);
        self::assertGreaterThan(0, $this->response);
        unset($this->response['id']);
        $this->assertResponse([
            'title' => $this->response['title'],
            'content' => $this->response['content'],
            'createdAt' => $this->response['createdAt'],
            'updatedAt' => $this->response['updatedAt'],
        ]);
    }

    public function testStoreIfShortTitle()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::DEFAULT_POST_SHORT_TITLE,
            'content' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testStoreIfShortContent()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testStoreWithoutTitle()
    {
        $this->post('/api/posts/store', [
            'title' => '',
            'content' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::TITLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testStoreWithoutContent()
    {
        $this->post('/api/posts/store', [
            'title' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
            'content' => '',
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testShow()
    {
        $this->get('/api/posts/show/' . ResponseMessages::EXISTING_ADMIN_POST_ID, $this->getAdminAuthClient());
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => ResponseMessages::EXISTING_ADMIN_POST_ID,
            'title' => ResponseMessages::EXISTING_ADMIN_TITLE,
            'content' => ResponseMessages::EXISTING_ADMIN_CONTENT,
            'createdAt' => ResponseMessages::ADMIN_POST_CREATED_AT,
            'updatedAt' => ResponseMessages::ADMIN_POST_UPDATED_AT,
        ]);
    }

    public function testUpdate()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [
            'title' => ResponseMessages::NEW_ADMIN_POST_TITLE,
            'content' => ResponseMessages::NEW_ADMIN_POST_CONTENT,

        ], $this->getAdminAuthClient());
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => $this->response['id'],
            'title' => $this->response['title'],
            'content' => $this->response['content'],
            'createdAt' => $this->response['createdAt'],
            'updatedAt' => $this->response['updatedAt'],
        ]);
    }

    public function testUpdateIfShortTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [
            'title' => ResponseMessages::DEFAULT_POST_SHORT_TITLE,
            'content' => ResponseMessages::EXISTING_ADMIN_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateIfShortContent()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [
            'title' => ResponseMessages::EXISTING_ADMIN_TITLE,
            'content' => ResponseMessages::DEFAULT_POST_SHORT_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutTitle()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [
            'title' => '',
            'content' => ResponseMessages::EXISTING_ADMIN_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::TITLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutContent()
    {
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_ADMIN_POST_ID, [
            'title' => ResponseMessages::EXISTING_ADMIN_TITLE,
            'content' => '',
            'updatedAt' => Carbon::now(),
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        static::assertArrayHasKey('errors', $this->response);
        static::assertStringContainsString(ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testDelete()
    {
        $this->delete('/api/posts/delete/' . ResponseMessages::EXISTING_ADMIN_POST_ID, $this->getAdminAuthClient());
        $this->assertResponseOk();
    }
}