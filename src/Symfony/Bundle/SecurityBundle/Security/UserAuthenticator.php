<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\Security;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * A decorator that delegates all method calls to the authenticator
 * manager of the current firewall.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 *
 * @final
 * @experimental in 5.3
 */
class UserAuthenticator implements UserAuthenticatorInterface
{
    private $firewallMap;
    private $userAuthenticators;
    private $requestStack;

    public function __construct(FirewallMap $firewallMap, ContainerInterface $userAuthenticators, RequestStack $requestStack)
    {
        $this->firewallMap = $firewallMap;
        $this->userAuthenticators = $userAuthenticators;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateUser(UserInterface $user, AuthenticatorInterface $authenticator, Request $request, array $badges = []): ?Response
    {
        return $this->getUserAuthenticator()->authenticateUser($user, $authenticator, $request, $badges);
    }

    private function getUserAuthenticator(): UserAuthenticatorInterface
    {
        $firewallConfig = $this->firewallMap->getFirewallConfig($this->requestStack->getMainRequest());
        if (null === $firewallConfig) {
            throw new LogicException('Cannot call authenticate on this request, as it is not behind a firewall.');
        }

        return $this->userAuthenticators->get($firewallConfig->getName());
    }
}
