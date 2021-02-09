<?php

namespace App\Tests\TestCases;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeatureTestCase extends WebTestCase
{
    protected const EXISTING_ADMIN_ID = 1;
    protected const EXISTING_ADMIN_NAME = 'admin';
    protected const EXISTING_ADMIN_EMAIL = 'admin@email.com';
    protected const EXISTING_ADMIN_PASSWORD = 'password';
    protected const EXISTING_USER_ID = 2;
    protected const EXISTING_USER_NAME = 'user';
    protected const EXISTING_USER_EMAIL = 'user@email.com';
    protected const EXISTING_USER_PASSWORD = 'password';
    protected const SHORT_NAME = 'S';
    protected const WEAK_PASSWORD = '1234567890';
    protected const VALID_PASSWORD = 'SoMeSeCuRePaSsWoRd54535251!!!';
    protected const VALID_NAME = 'SomeUsername';
    protected const VALID_EMAIL = 'NewValideamail@email.com';
    protected const INVALID_EMAIL = 'email.email.com';
    protected const EXPIRED_TOKEN = '18718e71b1c1411b395b94979424c7158a6e0c39fd18d9f3d94e76c5938c58749977a4f2d67d7320fe7874f2be2a09c36afc0c6b4271a873a0aaa2f5de92e24c';
    protected const NEW_USER_NAME = 'newName';
    protected const NEW_USER_EMAIL = 'newUserEmail@email.com';
    protected const ROLE_ID = 2;


    /** @var KernelBrowser|null */
    protected static ?KernelBrowser $anonClient = null;

    /** @var KernelBrowser|null */
    protected static ?KernelBrowser $adminAuthClient = null;

    /** @var KernelBrowser|null */
    protected static ?KernelBrowser $userAuthClient = null;

    /** @var int */
    protected int $statusCode = 0;

    /** @var array */
    protected array $response = [];

    /**
     * @return KernelBrowser
     */
    protected function getUserAuthClient(): KernelBrowser
    {
        if (self::$userAuthClient instanceof KernelBrowser) {
            return self::$userAuthClient;
        }

        self::$userAuthClient = self::getAnonymousClient();
        self::$userAuthClient->request('POST', '/api/login', [], [], [], json_encode([
                'email' => self::EXISTING_USER_EMAIL,
                'password' => self::EXISTING_USER_PASSWORD,
            ])
        );
        $data = json_decode(self::$userAuthClient->getResponse()->getContent(), true);

        self::$userAuthClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return self::$userAuthClient;
    }

    /**
     * @return KernelBrowser
     */
    protected function getAdminAuthClient(): KernelBrowser
    {
        if (self::$adminAuthClient instanceof KernelBrowser) {
            return self::$adminAuthClient;
        }

        self::$adminAuthClient = self::getAnonymousClient();
        self::$adminAuthClient->request('POST', '/api/login', [], [], [], json_encode([
                'email' => self::EXISTING_ADMIN_EMAIL,
                'password' => self::EXISTING_ADMIN_PASSWORD,
            ])
        );
        $data = json_decode(self::$adminAuthClient->getResponse()->getContent(), true);

        self::$adminAuthClient->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return self::$adminAuthClient;
    }

    /**
     * @return KernelBrowser
     */
    protected static function getAnonymousClient(): KernelBrowser
    {
        if (self::$anonClient instanceof KernelBrowser) {
            return self::$anonClient;
        }

        self::$anonClient = static::createClient();

        return self::$anonClient;
    }

    /**
     * @param string $uri
     * @param KernelBrowser|null $client
     */
    protected function get(string $uri, ?KernelBrowser $client = null): void
    {
        if (is_null($client)) {
            $client = self::getAnonymousClient();
        }

        $client->request('GET', $uri);
        $this->response($client);
    }

    /**
     * @param string $uri
     * @param array $params
     * @param KernelBrowser|null $client
     */
    protected function post(string $uri, array $params, ?KernelBrowser $client = null): void
    {
        if (is_null($client)) {
            $client = self::getAnonymousClient();
        }

        $content = json_encode($params);
        $client->request('POST', $uri, [], [], [], $content);
        $this->response($client);
    }

    /**
     * @param string $uri
     * @param array $params
     * @param KernelBrowser|null $client
     */
    protected function put(string $uri, array $params, ?KernelBrowser $client = null): void
    {
        if (is_null($client)) {
            $client = self::getAnonymousClient();
        }

        $content = json_encode($params);
        $client->request('PUT', $uri, [], [], [], $content);
        $this->response($client);
    }

    /**
     * @param string $uri
     * @param KernelBrowser|null $client
     */
    protected function delete(string $uri, ?KernelBrowser $client = null): void
    {
        if (is_null($client)) {
            $client = self::getAnonymousClient();
        }

        $client->request('DELETE', $uri);
        $this->response($client);
    }

    /**
     * @param array $expectedResponse
     */
    protected function assertResponse(array $expectedResponse): void
    {
        static::assertEquals($expectedResponse, $this->response);
    }

    /**
     * @param int $code
     */
    protected function assertStatusCode(int $code): void
    {
        static::assertEquals($code, $this->statusCode);
    }

    protected function assertResponseOk(): void
    {
        static::assertEquals(Response::HTTP_OK, $this->statusCode);
    }

    /**
     * @param KernelBrowser $client
     */
    private function response(KernelBrowser $client): void
    {
        $this->response = [];
        $response = $client->getResponse();
        $this->statusCode = $response->getStatusCode();

        if (!$response instanceof JsonResponse) {
            $this->response = [];
        }

        $this->response = json_decode($response->getContent(), true);
    }
}

