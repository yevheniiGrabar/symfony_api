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
        $newData = [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ];

        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());

        $this->assertResponseOk();
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => $newData['name'],
            'email' => $newData['email'],
            'isAdmin' => false,
        ]);
    }

    public function testUpdateWithExistingEmail()
    {
        $newData = [
            'name' => self::NEW_USER_NAME,
            'email' => self::EXISTING_ADMIN_EMAIL,
            'password' => self::VALID_PASSWORD,
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());

        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithWeakPassword()
    {
        $newData = [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::WEAK_PASSWORD,
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithShortName()
    {
        $newData = [
            'name' => self::SHORT_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithoutName()
    {
        $newData = [
            'name' => '',
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    public function testUpdateWithoutEmail()
    {
        $newData = [
            'name' => self::NEW_USER_NAME,
            'email' => '',
            'password' => self::VALID_PASSWORD,
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithoutPassword()
    {
        $newData = [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => '',
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
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

    }

    public function testUpdateAnotherUser()
    {
        $this->getUserAuthClient();

        $this->put('/api/users/update/' . self::EXISTING_ADMIN_ID, [], $this->getUserAuthClient());
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteAnotherUser()
    {
        $this->getUserAuthClient();
        $this->delete('/api/users/delete/' . self::EXISTING_ADMIN_ID,);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN);
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
