<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class UsersApiTests extends FeatureTestCase
{
    public function testStore(): void
    {
        $this->post('/api/users/store', [], $this->getUserAuthClient());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']
        );
    }

    public function testShow(): void
    {
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        self::assertResponseOk();
        self::assertResponse([
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

        self::assertResponseOk();
        self::assertResponse([
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

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::EMAIL_ALREADY_IN_USE_MESSAGE, $this->response['errors']
        );
    }

    public function testUpdateWithWeakPassword()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::WEAK_PASSWORD,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::PASSWORD_IS_COMPROMISED_MESSAGE, $this->response['errors']
        );
    }

    public function testUpdateWithShortName()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::SHORT_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::NAME_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutName()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => '',
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::NAME_IS_REQUIRE_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutEmail()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => '',
            'password' => self::VALID_PASSWORD,
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::EMAIL_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateWithoutPassword()
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => '',
        ], $this->getUserAuthClient());

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::PASSWORD_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testDelete(): void
    {
        $this->delete('/api/users/delete/' . self::EXISTING_USER_ID, $this->getUserAuthClient());
        self::assertResponseOk();
    }

    public function testShowAnotherUser()
    {
        $this->getUserAuthClient();
        $this->get('/api/users/show/' . self::EXISTING_ADMIN_ID, $this->getUserAuthClient());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }

    public function testUpdateAnotherUser()
    {
        $this->getUserAuthClient();
        $this->put('/api/users/update/' . self::EXISTING_ADMIN_ID, [], $this->getUserAuthClient());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
    }

    public function testDeleteAnotherUser()
    {
        $this->getUserAuthClient();
        $this->delete('/api/users/delete/' . self::EXISTING_ADMIN_ID,);
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::ACCESS_DENIED_MESSAGE, $this->response['errors']);
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
        self::assertFalse($response['isAdmin']);
    }
}
