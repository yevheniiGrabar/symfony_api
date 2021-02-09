<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
use App\Tests\TestCases\FeatureTestCase;
use Carbon\Carbon;

class PostsApiTests extends FeatureTestCase
{
    public function testStoreIfUser()
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
    public function testShowIfUser()
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

    public function testDelete()
    {
        $this->delete('/api/posts/delete/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        $this->assertResponseOk();
    }
}