<?php

namespace NS\SecurityBundle\Auth;

use \Doctrine\ORM\NoResultException;
use \Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use \Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use \Symfony\Component\Security\Core\Exception\BadCredentialsException;
use \Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use \Symfony\Component\Security\Core\Role\SwitchUserRole;
use \Symfony\Component\Security\Core\User\UserCheckerInterface;
use \Symfony\Component\Security\Core\User\UserInterface;
use \Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Description of UsernamePasswordPracticeAuthenticationProvider
 *
 * @author gnat
 */
class AdminAuthenticationProvider extends UserAuthenticationProvider
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var bool
     */
    private $hideUserNotFoundException;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @param UserProviderInterface $userProvider
     * @param UserCheckerInterface $userChecker
     * @param $providerKey
     * @param EncoderFactoryInterface $encoderFactory
     * @param bool $hideUserNotFoundExceptions
     */
    public function __construct($userProvider, $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);
        $this->encoderFactory = $encoderFactory;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->hideUserNotFoundException = $hideUserNotFoundExceptions;
    }

    /**
     * @param string $username
     * @param UsernamePasswordToken $token
     * @return null|UserInterface
     */
    public function retrieveUser($username, UsernamePasswordToken $token)
    {
        try {
            return $this->userProvider->loadUserByUsername($username);
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param TokenInterface $token
     * @return null|UsernamePasswordToken
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $adminUsername = $token->getUsername();
        if ($token->hasAttribute('desired_user')) {
            $username = $token->getAttribute('desired_user');
        }

        try {
            $adminUser = $this->retrieveUser($adminUsername, $token);
            $user = (empty($username)) ? $adminUser : $this->retrieveUser($username, $token);
        } catch (UsernameNotFoundException $notFound) {
            if ($this->hideUserNotFoundException) {
                throw new BadCredentialsException('Bad credentials', 0, $notFound);
            }

            throw $notFound;
        }

        if (!$adminUser instanceof UserInterface) {
            throw new AuthenticationServiceException('retrieveUser() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->checkAuthentication($adminUser, $token);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundException) {
                throw new BadCredentialsException('Bad credentials', 0, $e);
            }

            throw $e;
        }

        $attributes = $token->getAttributes();
        $roles = $user->getRoles();

        if ($token->hasAttribute('desired_user')) {
            $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', new UsernamePasswordToken($adminUser, $adminUser->getPassword(), $this->providerKey, $adminUser->getRoles()));
            unset($attributes['desired_user']);
        }

        $authenticatedToken = new UsernamePasswordToken($user, $user->getPassword(), $this->providerKey, $roles);
        $authenticatedToken->setAttributes($attributes);
        return $authenticatedToken;
    }

    /**
     * @param UserInterface $user
     * @param UsernamePasswordToken $token
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();

        if ($currentUser instanceof UserInterface) { // this happens if we were already logged in
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ("" === ($presentedPassword = $token->getCredentials())) {
                throw new BadCredentialsException('The presented password cannot be empty.');
            }

            if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }

        if ($token->hasAttribute('desired_user')) {
            $roles = $user->getRoles();
            if (!in_array('ROLE_ALLOWED_TO_SWITCH', $roles)) {
                throw new BadCredentialsException('You are not allowed to login as other users.');
            }
        }
    }
}
