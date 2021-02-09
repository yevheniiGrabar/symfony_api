<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
use App\Services\UserRequestValidator;
use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthApiTests extends FeatureTestCase
{
    public function testRegister()
    {
        $this->post('/api/register', [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        self::assertArrayHasKey('id', $this->response);
        self::assertGreaterThan(0, $this->response);
        unset($this->response['id']);
        $this->assertResponseOk();
        $this->assertResponse([
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => false,
        ]);
    }

    public function testRegisterWithExistingEmail()
    {
        $this->post('/api/register', [
            'name' => self::NEW_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::EMAIL_ALREADY_IN_USE_MESSAGE, $this->response['errors']
        );
    }

    public function testRegisterWithWeakPassword()
    {
        $this->post('/api/register', [
            'name' => self::NEW_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::WEAK_PASSWORD,
            'role_id' => self::ROLE_ID
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::PASSWORD_IS_COMPROMISED_MESSAGE, $this->response['errors']
        );
    }

    public function testRegisterWithoutName()
    {
        $this->post('/api/register', [
            'name' => '',
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::NAME_IS_REQUIRE_MESSAGE, $this->response['errors']
        );
    }

    public function testRegisterWithoutShortName()
    {
        $this->post('/api/register', [
            'name' => self::SHORT_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::NAME_IS_TOO_SHORT_MESSAGE, $this->response['errors']
        );
    }

    public function testRegisterWithoutEmail()
    {
        $this->post('/api/register', [
            'name' => self::EXISTING_USER_NAME,
            'email' => '',
            'password' => self::VALID_PASSWORD
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::EMAIL_IS_REQUIRED_MESSAGE, $this->response['errors']
        );
    }

    public function testRegisterWithoutPassword()
    {
        $this->post('/api/register', [
            'name' => self::SHORT_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => '',
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::PASSWORD_IS_REQUIRED_MESSAGE, $this->response['errors']
        );
    }

    public function testLogin()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $this->assertResponseOk();
        self::assertArrayHasKey('token', $this->response);
        self::assertArrayHasKey('refresh_token', $this->response);
        self::assertGreaterThan(0, strlen($this->response['token']));
        self::assertGreaterThan(0, strlen($this->response['refresh_token']));
    }

    public function testRefreshAction()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $this->assertResponseOk();
        $this->post('/api/token/refresh', [
            'refresh_token' => $this->response['refresh_token']
        ]);
        $this->assertResponseOk();
        self::assertArrayHasKey('token', $this->response);
        self::assertArrayHasKey('refresh_token', $this->response);
        self::assertGreaterThan(0, strlen($this->response['token']));
        self::assertGreaterThan(0, strlen($this->response['refresh_token']));
        self::$anonClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->response['token']));
        $this->get('/api/current');
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ]);
        self::$anonClient->setServerParameter('HTTP_Authorization', '');
    }

    public function testRefreshActionIfExpiredRefreshToken()
    {
        $this->post('/api/token/refresh', [
            'refresh_token' => self::EXPIRED_TOKEN
        ]);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::EXPIRED_REFRESH_TOKEN_MESSAGE, $this->response['errors']
        );
    }

    public function testLoginWithIncorrectEmail()
    {
        $this->post('/api/login', [
            'email' => self::INVALID_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::USER_NOT_FOUND_MESSAGE, $this->response['errors']);
    }

    public function testLoginWithIncorrectPassword()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::PASSWORD_IS_INVALID_MESSAGE, $this->response['errors']
        );
    }

    public function testLoginWithoutPassword()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => '',
        ]);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::PASSWORD_IS_INVALID_MESSAGE, $this->response['errors']
        );
    }

    public function testLoginWithoutEmail()
    {
        $this->post('/api/login', [
            'email' => '',
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::USER_NOT_FOUND_MESSAGE, $this->response['errors']);
    }

    public function testRefreshActionAfterUpdateEmail()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $this->assertResponseOk();
        $refreshToken = $this->response['refresh_token'];
        self::$anonClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->response['token']));
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertResponseOk();
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => false,
        ]);
        self::$anonClient->setServerParameter('HTTP_Authorization', '');
        $this->post('/api/token/refresh', [
            'refresh_token' => $refreshToken
        ]);
        $this->assertResponseOk();
        self::$anonClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->response['token']));
        $this->get('/api/users/show/' . self::EXISTING_USER_ID);
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => false,
        ]);
        self::$anonClient->setServerParameter('HTTP_Authorization', '');
    }
}
