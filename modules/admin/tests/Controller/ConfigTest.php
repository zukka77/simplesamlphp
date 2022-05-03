<?php

declare(strict_types=1);

namespace SimpleSAML\Module\admin\Test\Controller;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Module\admin\Controller;
use SimpleSAML\Session;
use SimpleSAML\Utils;
use SimpleSAML\XHTML\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set of tests for the controllers in the "admin" module.
 *
 * @covers \SimpleSAML\Module\admin\Controller\Config
 * @package SimpleSAML\Test
 */
class ConfigTest extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected Configuration $config;

    /** @var \SimpleSAML\Utils\Auth */
    protected Utils\Auth $authUtils;

    /** @var \SimpleSAML\Session */
    protected Session $session;


    /**
     * Set up for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Configuration::loadFromArray(
            [
                'module.enable' => ['admin' => true],
                'secretsalt' => 'defaultsecretsalt',
                'admin.checkforupdates' => true,
            ],
            '[ARRAY]',
            'simplesaml'
        );
        Configuration::setPreLoadedConfig($this->config, 'config.php');

        $this->authUtils = new class () extends Utils\Auth {
            public function requireAdmin(): void
            {
                // stub
            }
        };

        $session = $this->createMock(Session::class);
        $session->method('getData')->willReturn(['tag_name' => 'v1.18.7', 'html_url' => 'https://example.org']);

        /** @var \SimpleSAML\Session $session */
        $this->session = $session;
    }


    /**
     */
    public function testDiagnostics(): void
    {
        $_SERVER['REQUEST_URI'] = '/module.php/admin/diagnostics';
        $request = Request::create(
            '/diagnostics',
            'GET'
        );

        $c = new Controller\Config($this->config, $this->session);
        $c->setAuthUtils($this->authUtils);
        $response = $c->diagnostics($request);

        $this->assertTrue($response->isSuccessful());
    }


    /**
     */
    public function testMain(): void
    {
        $_SERVER['REQUEST_URI'] = '/module.php/admin';
        $request = Request::create(
            '/',
            'GET'
        );

        $c = new Controller\Config($this->config, $this->session);
        $c->setAuthUtils($this->authUtils);
        $response = $c->main($request);

        $this->assertTrue($response->isSuccessful());
    }


    /**
     */
    public function testPhpinfo(): void
    {
        $_SERVER['REQUEST_URI'] = '/module.php/admin/phpinfo';
        $request = Request::create(
            '/phpinfo',
            'GET'
        );

        $c = new Controller\Config($this->config, $this->session);
        $c->setAuthUtils($this->authUtils);
        $response = $c->phpinfo($request);

        $this->assertTrue($response->isSuccessful());
    }
}
