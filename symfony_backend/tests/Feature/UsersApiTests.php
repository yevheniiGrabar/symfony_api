<?php

namespace App\Tests\Feature;

use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class UsersApiTests extends FeatureTestCase
{
    public function testStore(): void
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $this->post('/api/users/store', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testShow(): void
    {
        $this->loginAsUser();
        $response = $this->getArrayResponse();
        $token = $response['token'];

        $this->get('/api/users/show/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $expectedResponse = [
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdate(): void
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newEmail = $this->getNonExistingValidEmail();
        $newName = 'SomeName';
        $newPassword = 'NeWPasWord!2341**';
        $newData = [
            'name' => $newName,
            'email' => $newEmail,
            'password' => $newPassword,
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $response = $this->getArrayResponse();
        $this->assertResponseOk();
        $expectedResponse = [
            'id' => $newUserData['id'],
            'name' => $newName,
            'email' => $newEmail,
            'isAdmin' => false
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateIfAdminRole()
    {
        $this->loginAsUser();
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $newData = [
            'name' => 'SomeName',
            'email' => 'newEmail@email.com',
            'password' => 'NeWPasWord!2341**',
            'role_id' => 1,
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $response = $this->getArrayResponse();
        $this->assertResponseOk();
        $this->assertFalse($response['isAdmin']);
    }

    public function testUpdateWithExistingEmail()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newName = 'SomeName';
        $newPassword = 'NeWPasWord!2341**';
        $newData = [
            'name' => $newName,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => $newPassword
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithWeakPassword()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newEmail = $this->getNonExistingValidEmail();
        $newName = 'SomeName';
        $newData = [
            'name' => $newName,
            'email' => $newEmail,
            'password' => '1111'
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithShortName()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newEmail = $this->getNonExistingValidEmail();
        $newPassword = 'NeWPasWord!2341**';
        $newData = [
            'name' => 'S',
            'email' => $newEmail,
            'password' => $newPassword
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithoutName()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newEmail = $this->getNonExistingValidEmail();
        $newPassword = 'NeWPasWord!2341**';
        $newData = [
            'name' => '',
            'email' => $newEmail,
            'password' => $newPassword
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    }

    public function testUpdateWithoutEmail()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newPassword = 'NeWPasWord!2341**';
        $newData = [
            'name' => 'SomeName',
            'email' => '',
            'password' => $newPassword
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateWithoutPassword()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $newEmail = $this->getNonExistingValidEmail();
        $newData = [
            'name' => 'SomeName',
            'email' => $newEmail,
            'password' => '',
        ];
        $this->put('/api/users/update/' . $newUserData['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testDelete(): void
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $this->delete('/api/users/delete/' . $newUserData['id'], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseOk();
    }

    public function testShowAnotherUser()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateAnotherUser()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteAnotherUser()
    {
        $newUserData = $this->registerAndLoginAsNewUser();
        $this->delete('/api/users/delete/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newUserData['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }
}

