<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
use App\Tests\TestCases\FeatureTestCase;
use Carbon\Carbon;

class AdminPostsApiTests extends FeatureTestCase
{
//    public function testStore()
//    {
//        $this->post('/api/posts/store', [
//            'title' => ResponseMessages::EXISTING_ADMIN_TITLE,
//            'content' => ResponseMessages::EXISTING_ADMIN_CONTENT,
//        ], $this->getAdminAuthClient());
//
//        self::assertArrayHasKey('id', $this->response);
//        self::assertGreaterThan(0, $this->response);
//        //unset($this->response['id']);
//        $this->assertResponse([
//            'title' => ResponseMessages::EXISTING_ADMIN_TITLE,
//            'content' => ResponseMessages::EXISTING_ADMIN_CONTENT,
//            'createdAt' => Carbon::now(),
//            'updatedAt' => Carbon::now(),
//        ]);
//    }

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
        $this->put('/api/posts/update/' . ResponseMessages::EXISTING_USER_POST_ID, [
            'title' => ResponseMessages::NEW_ADMIN_POST_TITLE,
            'content' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
            'updatedAt' => Carbon::now(),
        ], $this->getAdminAuthClient());
        $response = $this->response;
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => ResponseMessages::EXISTING_ADMIN_POST_ID,
            'title' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
            'content' => ResponseMessages::NEW_ADMIN_POST_CONTENT,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }
//
//    public function testDelete()
//    {
//        //
//    }

}