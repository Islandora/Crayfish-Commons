<?php

namespace Islandora\Crayfish\Commons\Syn;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class JwtUserProvider
 * @package Islandora\Crayfish\Commons\Syn
 */
class JwtUserProvider implements UserProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        // We can't load by username so return nothing.
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $user = new JwtUser($user->getUsername(), $user->getRoles());
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return JwtUser::class == $class;
    }
}
