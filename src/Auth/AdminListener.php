<?php
namespace NS\SecurityBundle\Auth;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Description of PracticeListener
 *
 * @author gnat
 */
class AdminListener extends AbstractAuthenticationListener
{
    /**
     * {@inheritdoc}
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, SessionAuthenticationStrategyInterface $sessionStrategy, HttpUtils $httpUtils, $providerKey, AuthenticationSuccessHandlerInterface $successHandler, AuthenticationFailureHandlerInterface $failureHandler, array $options, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $params = array(
            'username_parameter' => '_username',
            'password_parameter' => '_password',
            'user_parameter' => '_user',
            'csrf_parameter' => '_csrf_token',
            'intention' => 'authenticate',
            'post_only' => true,
        );

        parent::__construct($tokenStorage, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler, $failureHandler, array_merge($params, $options), $logger, $dispatcher);
    }

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request)
    {
        if ($this->options['post_only'] && !$request->isMethod('post')) {
            return false;
        }

        return parent::requiresAuthentication($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $this->logger->info("adminListener attempting authentication!");
        if ($this->options['post_only'] && 'post' !== strtolower($request->getMethod())) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Authentication method not supported: %s.', $request->getMethod()));
            }

            return null;
        }

        $username = trim($request->get($this->options['username_parameter']));
        $password = $request->get($this->options['password_parameter']);
        $user = $request->get($this->options['user_parameter']);
        $token = new UsernamePasswordToken($username, $password, $this->providerKey);

        if (null !== $user) {
            $token->setAttribute('desired_user', $user);
        }

        return $this->authenticationManager->authenticate($token);
    }
}
