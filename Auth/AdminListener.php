<?php
namespace NS\SecurityBundle\Auth;

use \Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use \Symfony\Component\Security\Http\HttpUtils;
use \Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

use \Symfony\Component\Security\Core\SecurityContextInterface;
use \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpKernel\Log\LoggerInterface;
use \Symfony\Component\EventDispatcher\EventDispatcherInterface;
use \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Description of PracticeListener
 *
 * @author gnat
 */
class AdminListener extends AbstractAuthenticationListener
{
    private $csrfProvider;

    /**
     * {@inheritdoc}
     */
    public function __construct(SecurityContextInterface $securityContext, 
                                AuthenticationManagerInterface $authenticationManager, 
                                SessionAuthenticationStrategyInterface $sessionStrategy, 
                                HttpUtils $httpUtils, 
                                $providerKey, 
                                AuthenticationSuccessHandlerInterface $successHandler, 
                                AuthenticationFailureHandlerInterface $failureHandler, 
                                array $options = array(), 
                                LoggerInterface $logger = null, 
                                EventDispatcherInterface $dispatcher = null, 
                                CsrfProviderInterface $csrfProvider = null)
    {
        parent::__construct($securityContext, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler, $failureHandler, array_merge(array(
            'username_parameter' => '_username',
            'password_parameter' => '_password',
            'user_parameter'     => '_user',
            'csrf_parameter'     => '_csrf_token',
            'intention'          => 'authenticate',
            'post_only'          => true,
        ), $options), $logger, $dispatcher);

        $this->csrfProvider = $csrfProvider;
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

        if (null !== $this->csrfProvider) {
            $csrfToken = $request->get($this->options['csrf_parameter'], null, true);

            if (false === $this->csrfProvider->isCsrfTokenValid($this->options['intention'], $csrfToken)) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
        }

        $username = trim($request->get($this->options['username_parameter'], null, true));
        $password = $request->get($this->options['password_parameter'], null, true);
        $user     = $request->get($this->options['user_parameter'], null, true);
        $t        = new UsernamePasswordToken($username, $password, $this->providerKey);

        if(null != $user)
            $t->setAttribute('desired_user',$user);
        
        $request->getSession()->set(SecurityContextInterface::LAST_USERNAME, $username);

        return $this->authenticationManager->authenticate($t);
    }
}