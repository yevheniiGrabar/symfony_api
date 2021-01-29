<?php

namespace App\Tests\TestCases;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;
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
    protected const WEAK_PASSWORD = '111';
    protected const VALID_PASSWORD = 'SoMeSeCuRePaSsWoRd54535251!!!';
    protected const VALID_NAME = 'SomeUsername';
    protected const EXPIRED_TOKEN = '18718e71b1c1411b395b94979424c7158a6e0c39fd18d9f3d94e76c5938c58749977a4f2d67d7320fe7874f2be2a09c36afc0c6b4271a873a0aaa2f5de92e24c';

    /** @var KernelBrowser|null */
    private static ?KernelBrowser $client = null;

    /**
     * @return KernelBrowser
     */
    protected static function getClient(): KernelBrowser
    {
        if (self::$client instanceof KernelBrowser) {
            return self::$client;
        }

        self::$client = static::createClient();

        return self::$client;
    }

    protected function loginAsAdmin(): void
    {
        $this->loginAsUser(self::EXISTING_ADMIN_EMAIL, self::EXISTING_ADMIN_PASSWORD);
    }

    /**
     * @param string $email
     * @param string $password
     */
    protected function loginAsUser(
        string $email = self::EXISTING_USER_EMAIL,
        string $password = self::EXISTING_USER_PASSWORD
    ): void
    {
        $this->post('/api/login', [
            'email' => $email,
            'password' => $password
        ]);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @return string
     */
    protected function registerAsUser(
        string $name = self::VALID_NAME,
        string $email = '',
        string $password = self::VALID_PASSWORD
    ): string
    {
        if ($email == '') {
            $email = $this->getNonExistingValidEmail();
        }

        $this->post('/api/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        return $email;
    }

    /**
     * @return array
     */
    protected function getArrayResponse(): array
    {
        $response = self::getClient()->getResponse();

        if (!$response instanceof JsonResponse) {
            return [];
        }

        return json_decode($response->getContent(), true);
    }

    protected function assertResponseOk(): void
    {
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    /**
     * @param int $code
     */
    protected function assertResponseStatus(int $code): void
    {
        $this->assertEquals($code, self::getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param string $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param string|null $content
     * @param bool $changeHistory
     * @return Crawler|null
     */
    protected function post(
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): ?Crawler
    {
        return self::getClient()->request('POST', $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    /**
     * @param string $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param string|null $content
     * @param bool $changeHistory
     * @return Crawler|null
     */
    protected function get(
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): ?Crawler
    {
        return self::getClient()->request('GET', $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    /**
     * @param string $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param string|null $content
     * @param bool $changeHistory
     * @return Crawler|null
     */
    protected function put(
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): ?Crawler
    {
        return self::getClient()->request('PUT', $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    /**
     * @param string $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param string|null $content
     * @param bool $changeHistory
     * @return Crawler|null
     */
    protected function delete(
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): ?Crawler
    {
        return self::getClient()->request('DELETE', $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    /**
     * @return string
     */
    protected function getNonExistingValidEmail(): string
    {
        return 'someEmail' . rand(0, 100) . microtime(true) . '@email.com';
    }

    /**
     * @return array
     */
    protected function registerAndLoginAsNewUser(): array
    {
        $email = $this->registerAsUser();
        $response = $this->getArrayResponse();
        $id = $response['id'];
        $this->loginAsUser($email, self::VALID_PASSWORD);
        $response = $this->getArrayResponse();
        $token = $response['token'];

        return [
            'id' => $id,
            'token' => $token
        ];
    }
}

