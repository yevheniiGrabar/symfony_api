<?php

namespace App\Tests\Feature;

use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminsApiTests extends FeatureTestCase
{
    public function testAdminStore()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => 2
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('id', $this->response);
        $this->assertGreaterThan(0, $this->response);
        unset($this->response['id']);
        $this->assertResponse([
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'isAdmin' => false
        ]);

    }

    public function testAdminStoreIfWrongRole()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '-1'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors',$this->response,'Role is required');
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfInvalidName()
    {
        $this->post('/api/users/store', [
            'name' => self::SHORT_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors',$this->response,'Name is too short');
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfExistingEmail()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors', $this->response,'This email is already in use');
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfWeakPassword()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => 'password',
            'role_id' => '2'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors',$this->response,'This password was compromised');
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminShow()
    {
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, $this->getAdminAuthClient());
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ]);
    }

    public function testAdminUpdate(): void
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '1'
        ], $this->getAdminAuthClient());

        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => true
        ]);
    }

    public function testAdminDelete()
    {
        $this->delete('/api/users/delete/' . self::EXISTING_USER_ID, $this->getAdminAuthClient());
        $this->assertResponseOk();
    }
}

