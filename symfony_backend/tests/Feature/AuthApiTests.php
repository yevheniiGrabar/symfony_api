<?php

namespace App\Tests\Feature;

use App\Tests\TestCases\FeatureTestCase;
use DAMA\DoctrineTestBundle\Behat\BehatListener;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo: Assert response messages
 */
class AuthApiTests extends FeatureTestCase
{
    public function testRegister()
    {
        $this->post('/api/register', [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertArrayHasKey('id', $this->response);
        $this->assertGreaterThan(0, $this->response);
        unset($this->response['id']);
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
        $this->assertArrayHasKey('errors', $this->response, self::EMAIL_ALREADY_IN_USE_MESSAGE);
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
        $this->assertArrayHasKey('errors', $this->response, self::PASSWORD_IS_TOO_SHORT_MESSAGE);
    }

    public function testRegisterWithoutName()
    {
        $this->post('/api/register', [
            'name' => '',
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, self::NAME_IS_REQUIRED_MESSAGE);
    }

    public function testRegisterWithoutShortName()
    {
        $this->post('/api/register', [
            'name' => self::SHORT_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, self::NAME_IS_TOO_SHORT_MESSAGE);
    }

    public function testRegisterWithoutEmail()
    {
        $this->post('/api/register', [
            'name' => self::EXISTING_USER_NAME,
            'email' => '',
            'password' => self::VALID_PASSWORD
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, self::EMAIL_IS_REQUIRED_MESSAGE);
    }

    public function testRegisterWithoutPassword()
    {
        $this->post('/api/register', [
            'name' => self::SHORT_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => '',
        ]);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response, self::PASSWORD_IS_REQUIRED_MESSAGE);
    }

    public function testLogin()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $this->assertArrayHasKey('token', $this->response);
        $this->assertArrayHasKey('refresh_token', $this->response);
        $this->assertGreaterThan(0, strlen($this->response['token']));
        $this->assertGreaterThan(0, strlen($this->response['refresh_token']));
    }

    /**
     * @todo: Check why does it return same access token after refresh
     */
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
        $this->assertArrayHasKey('token', $this->response);
        $this->assertArrayHasKey('refresh_token', $this->response);
        $this->assertGreaterThan(0, strlen($this->response['token']));
        $this->assertGreaterThan(0, strlen($this->response['refresh_token']));
        self::$anonClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->response['token']));
        $this->get('/api/current');
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
        $this->assertResponse([
            'errors' => 'Expired refresh token'
        ]);
        $this->assertArrayHasKey('errors', $this->response, self::EXPIRE_REFRESH_TOKEN_MESSAGE);
    }

    public function testLoginWithIncorrectEmail()
    {
        $this->post('/api/login', [
            'email' => self::INVALID_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND);
    }

    public function testLoginWithIncorrectPassword()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
        $this->assertArrayHasKey('errors', $this->response, self::PASSWORD_IS_INVALID_MESSAGE);
    }

    public function testLoginWithoutPassword()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => '',
        ]);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
        $this->assertArrayHasKey('errors', $this->response, self::PASSWORD_IS_INVALID_MESSAGE);
    }

    public function testLoginWithoutEmail()
    {
        $this->post('/api/login', [
            'email' => '',
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND);
    }

    public function testRefreshActionAfterUpdateEmail()
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $refreshToken = $this->response['refresh_token'];
        self::$anonClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->response['token']));
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
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
