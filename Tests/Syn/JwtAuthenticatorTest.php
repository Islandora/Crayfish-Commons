<?php

namespace Islandora\Crayfish\Commons\Tests\Syn;

use Islandora\Crayfish\Commons\Syn\JwtAuthenticator;
use Islandora\Crayfish\Commons\Syn\JwtFactory;
use Islandora\Crayfish\Commons\Syn\JwtUser;
use Islandora\Crayfish\Commons\Syn\JwtUserProvider;
use Islandora\Crayfish\Commons\Syn\SettingsParser;
use Islandora\Crayfish\Commons\Tests\AbstractCrayfishCommonsTestCase;
use Namshi\JOSE\SimpleJWS;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JwtAuthenticatorTest extends AbstractCrayfishCommonsTestCase
{

    private $simpleAuth;

    public function setUp(): void
    {
        parent::setUp();
        $this->simpleAuth = $this->getSimpleAuth();
    }

    private function getParser($site = null, $token = null)
    {
        if ($site === null) {
            $site = [
                'https://foo.com' => ['algorithm' => '', 'key' => '' , 'url' => 'https://foo.com']
            ];
        }
        if ($token === null) {
            $token = [
                'testtoken' => ['user' => 'test', 'roles' => ['1', '2'], 'token' => 'testToken']
            ];
        }
        $prophet = $this->prophesize(SettingsParser::class);
        $prophet->getStaticTokens()->willReturn($token);
        $prophet->getSites()->willReturn($site);
        return $prophet->reveal();
    }

    private function getJwtFactory($jwt, $fail = false)
    {
        $prophet = $this->prophesize(JwtFactory::class);
        if ($fail) {
            $prophet->load(Argument::any())->willThrow(\InvalidArgumentException::class);
        } else {
            $prophet->load(Argument::any())->willReturn($jwt);
        }
        return $prophet->reveal();
    }

    private function getUserProvider()
    {
        return new JwtUserProvider();
    }

    private function getSimpleAuth($bad_token = false)
    {
        $jwt = $this->prophesize(SimpleJWS::class)->reveal();
        $parser = $this->getParser();
        $jwtFactory = $this->getJwtFactory($jwt, $bad_token);
        return new JwtAuthenticator($parser, $jwtFactory);
    }

    /**
     * Utility function to ensure the index does not exist in array or is null.
     *
     * @param array $array
     *   The credential array.
     * @param string $index
     *   The associative array index.
     *
     * @return boolean
     *   Whether the index does not exist or is null.
     */
    private function unsetOrNull(array $array, $index)
    {
        return (!array_key_exists($index, $array) || is_null($array[$index]));
    }

    /**
     * Compare a credential array against what we return for invalid creds.
     *
     * @param $credentials
     *   Array with credentials.
     */
    private function checkInvalidCredentials($credentials)
    {
        $this->assertTrue($this->unsetOrNull($credentials, 'name'));
        $this->assertTrue($this->unsetOrNull($credentials, 'roles'));
        $this->assertTrue($this->unsetOrNull($credentials, 'jwt'));
        $this->assertFalse($this->unsetOrNull($credentials, 'token'));
    }

    public function testAuthenticationFailure()
    {
        $request = $this->prophesize(Request::class)->reveal();
        $exception = $this->prophesize(AuthenticationException::class)->reveal();

        $response = $this->simpleAuth->onAuthenticationFailure($request, $exception);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAuthenticationStart()
    {
        $request = $this->prophesize(Request::class)->reveal();
        $exception = $this->prophesize(AuthenticationException::class)->reveal();

        $response = $this->simpleAuth->start($request, $exception);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAuthenticationSuccess()
    {
        $request = $this->prophesize(Request::class)->reveal();
        $token = $this->prophesize(TokenInterface::class)->reveal();

        $response = $this->simpleAuth->onAuthenticationSuccess($request, $token, null);
        $this->assertNull($response);
    }

    public function testRememberMe()
    {
        $this->assertFalse($this->simpleAuth->supportsRememberMe());
    }

    /**
     * Get credential array from request.
     *
     * @param $request
     *   The request.
     * @return array|mixed
     *   Array of token parts.
     */
    private function getCredsHelper($request)
    {
        $credentials = $this->simpleAuth->getCredentials($request);
        return $credentials;
    }

    /**
     * Utility function to run the checkCredentials against submitted creds.
     *
     * @param $credentials
     *   Array of credentials.
     * @return boolean
     *   Whether the user is authorized or not.
     */
    private function checkCredsHelper($credentials)
    {
        $authorized = $this->simpleAuth->checkCredentials(
            $credentials,
            new JwtUser($credentials['name'], $credentials['roles'])
        );
        return $authorized;
    }

    public function testNoHeader()
    {
        $request = new Request();
        $this->assertFalse($this->simpleAuth->supports($request));
    }

    public function testHeaderNoBearer()
    {
        $request = new Request();
        $request->headers->set("Authorization", "foo");
        $this->assertFalse($this->simpleAuth->supports($request));
    }

    public function testHeaderBadToken()
    {
        $request = new Request();
        $request->headers->set("Authorization", "Bearer foo");
        $this->simpleAuth = $this->getSimpleAuth(true);
        $creds = $this->getCredsHelper($request);
        $this->checkInvalidCredentials($creds);
        $this->assertFalse($this->checkCredsHelper($creds));
    }

    /**
     * Takes an array of JWT parts and tries to authenticate against it.
     *
     * @param $data
     *   The array of JWT parameters.
     * @param bool $expired
     *   Whether the JWT has expired or not.
     * @return bool
     *   Whether the credentials authenticate or not.
     */
    public function headerTokenHelper($data, $expired = false)
    {
        $parser = $this->getParser();
        $request = new Request();
        $request->headers->set("Authorization", "Bearer foo");
        $prophet = $this->prophesize(SimpleJWS::class);
        $prophet->getPayload()->willReturn($data);
        $prophet->isExpired()->willReturn($expired);
        $prophet->isValid(Argument::any(), Argument::any())->willReturn(true);
        $jwt = $prophet->reveal();
        $jwtFactory = $this->getJwtFactory($jwt);
        $auth = new JwtAuthenticator($parser, $jwtFactory);
        $credentials = $auth->getCredentials($request);
        $user = new JwtUser($credentials['name'], $credentials['roles']);
        return $auth->checkCredentials($credentials, $user);
    }

    public function testHeaderTokenFields()
    {
        $data = [
            'webid' => 1,
            'iss' => 'https://foo.com',
            'sub' => 'charlie',
            'roles' => ['bartender', 'exterminator'],
            'iat' => 1,
            'exp' => 1,
        ];
        $this->assertTrue($this->headerTokenHelper($data));

        $missing = $data;
        unset($missing['webid']);
        $this->assertFalse($this->headerTokenHelper($missing));

        $missing = $data;
        unset($missing['iss']);
        $this->assertFalse($this->headerTokenHelper($missing));

        $missing = $data;
        unset($missing['sub']);
        $this->assertFalse($this->headerTokenHelper($missing));

        $missing = $data;
        unset($missing['roles']);
        $this->assertFalse($this->headerTokenHelper($missing));

        $missing = $data;
        unset($missing['iat']);
        $this->assertFalse($this->headerTokenHelper($missing));

        $missing = $data;
        unset($missing['exp']);
        $this->assertFalse($this->headerTokenHelper($missing));

        $this->assertFalse($this->headerTokenHelper($data, true));
    }

    public function jwtAuthHelper($data, $parser, $valid = true)
    {
        $request = new Request();
        $request->headers->set("Authorization", "Bearer foo");

        $prophet = $this->prophesize(SimpleJWS::class);
        $prophet->getPayload()->willReturn($data);
        $prophet->isExpired()->willReturn(false);
        $prophet->isValid(Argument::any(), Argument::any())->willReturn($valid);
        $jwt = $prophet->reveal();
        $jwtFactory = $this->getJwtFactory($jwt);
        $auth = new JwtAuthenticator($parser, $jwtFactory);
        $credentials = $auth->getCredentials($request);
        $this->assertNotNull($credentials);
        $this->assertEquals('charlie', $credentials['name']);
        $this->assertEquals('foo', $credentials['token']);
        $this->assertTrue(in_array('bartender', $credentials['roles']));
        $this->assertTrue(in_array('exterminator', $credentials['roles']));

        $user = $auth->getUser($credentials, $this->getUserProvider());
        $this->assertInstanceOf(JwtUser::class, $user);
        $this->assertEquals('charlie', $user->getUsername());
        $this->assertEquals(['bartender', 'exterminator'], $user->getRoles());
        return $auth->checkCredentials($credentials, $user);
    }

    public function testJwtAuthentication()
    {
        $data = [
            'webid' => 1,
            'iss' => 'https://foo.com',
            'sub' => 'charlie',
            'roles' => ['bartender', 'exterminator'],
            'iat' => 1,
            'exp' => 1,
        ];
        $parser = $this->getParser();
        $this->assertTrue($this->jwtAuthHelper($data, $parser));
    }

    public function testJwtAuthenticationInvalidJwt()
    {
        $data = [
            'webid' => 1,
            'iss' => 'https://foo.com',
            'sub' => 'charlie',
            'roles' => ['bartender', 'exterminator'],
            'iat' => 1,
            'exp' => 1,
        ];
        $parser = $this->getParser();
        $this->assertFalse($this->jwtAuthHelper($data, $parser, false));
    }

    public function testJwtAuthenticationNoSite()
    {
        $data = [
            'webid' => 1,
            'iss' => 'https://www.pattyspub.ca/',
            'sub' => 'charlie',
            'roles' => ['bartender', 'exterminator'],
            'iat' => 1,
            'exp' => 1,
        ];
        $parser = $this->getParser();
        $this->assertFalse($this->jwtAuthHelper($data, $parser));
    }

    public function testJwtAuthenticationDefaultSite()
    {
        $data = [
            'webid' => 1,
            'iss' => 'https://www.pattyspub.ca/',
            'sub' => 'charlie',
            'roles' => ['bartender', 'exterminator'],
            'iat' => 1,
            'exp' => 1,
        ];
        $site = [
            'default' => ['algorithm' => '', 'key' => '' , 'url' => 'default']
        ];
        $parser = $this->getParser($site);
        $this->assertTrue($this->jwtAuthHelper($data, $parser));
    }

    public function testStaticToken()
    {
        $auth = $this->getSimpleAuth();
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer testtoken');
        $credentials = $auth->getCredentials($request);
        $this->assertNotNull($credentials);
        $this->assertEquals('test', $credentials['name']);
        $this->assertEquals(['1', '2'], $credentials['roles']);
        $this->assertEquals('testToken', $credentials['token']);

        $user = $auth->getUser($credentials, $this->getUserProvider());
        $this->assertInstanceOf(JwtUser::class, $user);
        $this->assertEquals('test', $user->getUsername());
        $this->assertEquals(['1', '2'], $user->getRoles());

        $this->assertTrue($auth->checkCredentials($credentials, $user));
    }
}
