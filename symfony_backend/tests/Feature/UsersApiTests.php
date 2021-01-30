<?php

namespace App\Tests\Feature;

use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class UsersApiTests extends FeatureTestCase
{
    public function testStore(): void
    {
        $this->post('/api/users/store', [], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        $this->assertResponse([
            'errors' => 'Access denied'
        ]);
        $this->assertArrayHasKey('errors', $this->response, 'Access denied');
    }

    public function testShow(): void
    {
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ]);
    }

    public function testUpdate(): void
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => false,
        ]);
    }

    public function testUpdateWithExistingEmail()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::EXISTING_ADMIN_EMAIL,
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, 'Email already in use');
    }

    public function testUpdateWithWeakPassword()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::WEAK_PASSWORD,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, 'Password is too short');
    }

    public function testUpdateWithShortName()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::SHORT_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, 'Name is too short');
    }

    public function testUpdateWithoutName()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => '',
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response,'Name is required');
    }


    public function testUpdateWithoutEmail()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => '',
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response,'Email is required');
    }

    public function testUpdateWithoutPassword()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => '',
        ], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response,'Password is required');
    }


    public function testDelete(): void
    {
        $this->delete('/api/users/delete/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        $this->assertResponseOk();
    }

    public function testShowAnotherUser()
    {
        $this->getUserAuthClient();
        $this->get('/api/users/show/' . self::EXISTING_ADMIN_ID, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        $this->assertArrayHasKey('errors', $this->response,'Access denied');

    }

    public function testUpdateAnotherUser()
    {
        $this->getUserAuthClient();

        $this->put('/api/users/update/' . self::EXISTING_ADMIN_ID, [], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        $this->assertArrayHasKey('errors', $this->response, 'Access denied');
    }

    public function testDeleteAnotherUser()
    {
        $this->getUserAuthClient();
        $this->delete('/api/users/delete/' . self::EXISTING_ADMIN_ID,);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
        $this->assertArrayHasKey('errors', $this->response, 'Access denied');
    }

    public function testUpdateIfAdminRole()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::EXISTING_USER_PASSWORD,
        ]);

        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => 1,
        ], $this->getUserAuthClient());
        $response = $this->response;
        $this->assertFalse($response['isAdmin']);
    }
}
