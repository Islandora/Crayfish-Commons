<?php

namespace Islandora\Crayfish\Commons\Syn;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use InvalidArgumentException;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    protected $logger;
    protected $staticTokens;
    protected $sites;
    protected $jwtFactory;

    public function __construct(
        SettingsParser $parser,
        JwtFactory $jwtFactory,
        LoggerInterface $logger = null
    ) {
        if ($logger == null) {
            $this->logger = new NullLogger();
        } else {
            $this->logger = $logger;
        }

        $this->jwtFactory = $jwtFactory;
        $this->staticTokens = $parser->getStaticTokens();
        $this->sites = $parser->getSites();
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        // Check headers
        $token = $request->headers->get('Authorization');

        // Chop off the leading "bearer " from the token
        $token = substr($token, 7);
        $this->logger->debug("Token: $token");

        // Check if this is a static token
        if (isset($this->staticTokens[$token])) {
            $staticToken = $this->staticTokens[$token];
            return [
                'token' => $staticToken['token'],
                'jwt' => null,
                'name' => $staticToken['user'],
                'roles' => $staticToken['roles']
            ];
        }

        // Decode token
        try {
            $jwt = $this->jwtFactory->load($token);
        } catch (InvalidArgumentException $exception) {
            $this->logger->info('Invalid token. ' . $exception->getMessage());
            return [
              'token' => $token,
              'name' => null,
              'roles' => null,
            ];
        }

        // Check correct properties
        $payload = $jwt->getPayload();

        return [
            'token' => $token,
            'jwt' => $jwt,
            'name' => $payload['sub'] ?? NULL,
            'roles' => $payload['roles'] ?? NULL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return new JwtUser($credentials['name'], $credentials['roles']);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($credentials['name'] === null) {
            // No name means the token was invalid.
            $this->logger->info("Token was invalid:");
            return false;
        }

        // If this is a static token then no more verification needed
        if ($credentials['jwt'] === null) {
            $this->logger->info('Logged in with static token: ' . $credentials['name']);
            return true;
        }

        $jwt = $credentials['jwt'];
        $payload = $jwt->getPayload();
        // Check and warn of all missing claims before rejecting.
        $missing_claim = false;
        if (!isset($payload['webid'])) {
            $this->logger->info('Token missing webid');
            $missing_claim = true;
        }
        if (!isset($payload['iss'])) {
            $this->logger->info('Token missing iss');
            $missing_claim = true;
        }
        if (!isset($payload['sub'])) {
            $this->logger->info('Token missing sub');
            $missing_claim = true;
        }
        if (!isset($payload['roles'])) {
            $this->logger->info('Token missing roles');
            $missing_claim = true;
        }
        if (!isset($payload['iat'])) {
            $this->logger->info('Token missing iat');
            $missing_claim = true;
        }
        if (!isset($payload['exp'])) {
            $this->logger->info('Token missing exp');
            $missing_claim = true;
        }
        if ($missing_claim) {
            // If any claim is missing
            return false;
        }
        if ($jwt->isExpired()) {
            $this->logger->info('Token expired');
            return false;
        }

        $url = $payload['iss'];
        if (isset($this->sites[$url])) {
            $site = $this->sites[$url];
        } elseif (isset($this->sites['default'])) {
            $site = $this->sites['default'];
        } else {
            $this->logger->info('No site matches');
            return false;
        }

        return $jwt->isValid($site['key'], $site['algorithm']);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => $exception->getMessageKey(),
        );
        return new JsonResponse($data, 403);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required',
        );
        return new JsonResponse($data, 401);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        // Check headers
        $token = $request->headers->get('Authorization');
        if (!$token) {
            $this->logger->info('Token missing');
            return false;
        }
        if (0 !== strpos(strtolower($token), 'bearer ')) {
            $this->logger->info('Token malformed');
            return false;
        }
        return true;
    }
}
