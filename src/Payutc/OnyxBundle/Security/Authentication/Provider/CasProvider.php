<?php

namespace Payutc\OnyxBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Ginger\Client\GingerClient;

use Payutc\Client\JsonException;
use Payutc\OnyxBundle\Security\Authentication\Token\CasToken;
use Payutc\OnyxBundle\Security\Cas;
use Payutc\OnyxBundle\Entity\User;

class CasProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cacheDir;
    private $entityManager;
    private $encoderFactory;
    private $casUrl;
    private $gingerUrl;
    private $gingerKey;
    private $payutcClient;
    private $mailerFromName;
    private $mailerFromEmail;
    private $mailerSubject;

    public function __construct(UserProviderInterface $userProvider, $cacheDir, $entityManager, $encoderFactory, $casUrl, $gingerUrl, $gingerKey, $payutcClient, $mailerFromName, $mailerFromEmail, $mailerSubject)
    {
        $this->userProvider     = $userProvider;
        $this->cacheDir         = $cacheDir;
        $this->entityManager    = $entityManager;
        $this->encoderFactory   = $encoderFactory;
        $this->casUrl           = $casUrl;
        $this->gingerUrl        = $gingerUrl;
        $this->gingerKey        = $gingerKey;
        $this->payutcClient     = $payutcClient;
        $this->mailerFromName   = $mailerFromName;
        $this->mailerFromEmail  = $mailerFromEmail;
        $this->mailerSubject    = $mailerSubject;
    }

    public function authenticate(TokenInterface $token)
    {
        if ($token->getUser() instanceof User) {
            return $token;
        }

        // #### HEAD ####
        $role = array("ROLE_USER");
        
        if($token->admin) {
            try {
                $userLogin = $this->payutcClient->loginCas($token->ticket, $token->service);
            } catch (\Exception $e) {
                throw new AuthenticationException('The CAS authentication failed.');
            }
            
            // Define role (USER/ADMIN/SUPER_ADMIN)
            // USER => Pas de droit GESARTICLE
            // ADMIN => GESARTICLE sur certaines fundations
            // SUPERADMIN => GESARTICLE sur toutes les fundations
            if($this->payutcClient->isAdmin()) {
                $role = array("ROLE_SUPER_ADMIN");
            } else {
                try {
                    if(count($this->payutcClient->getFundations()) > 0) {
                        $role = array("ROLE_ADMIN");
                    }
                } catch (JsonException $e) {
                    $role = array("ROLE_USER");
                }
            }
            
            
        } else {
            $cas = new Cas($this->casUrl);
            try {
                $userLogin = $cas->authenticate($token->ticket, $token->service);
            } catch (\Exception $e) {
                throw new AuthenticationException('The CAS authentication failed (ticket validation). $token->ticket, $token->service');
            }
        }
        $ginger = new GingerClient($this->gingerKey, $this->gingerUrl);
        $userInfo = $ginger->getUser($userLogin);

        // #### CONFIG ####

        try {
            $user = $this->userProvider->loadUserByUsername($userInfo->mail);
        } catch (UsernameNotFoundException $e) {
            // User doesn't already exist, we need to create him an account
            $user = new User();
            $user->setEmail($userInfo->mail);
            $user->setFirstname($userInfo->prenom);
            $user->setName($userInfo->nom);
            $user->setLogin($userLogin);

            $password = $this->generatePassword(8);
            $user->setPassword($password);
            $user->encryptPassword($this->encoderFactory->getEncoder($user));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $message = \SwiftMessage::newInstance()
                ->setSubject($this->mailerSubject)
                ->setFrom(array($this->mailerFromEmail => $this->mailerFromName))
                ->setTo($user->getEmail())
                ->setBody($this->renderView('PayutcOnyxBundle:Authentication/Cas:registration.mail.html.twig', array(
                    'firstname' => $user->getFirstname(),
                    'name' => $user->getName(),
                    'login' => $user->getEmail(),
                    'password' => $password
                )), 'text/html')
            ;

            $this->get('mailer')->send($message);
        }
        
        if ($user) {
            //$authenticatedToken = new CasToken($user->getRoles());
            $authenticatedToken = new CasToken($role);
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The CAS authentication failed.');
    }

    /**
     * Generate a randomly generated password based on a php's str_shuffle method called on a characters list.
     *
     * @param int $length
     * @return string
     */
    protected function generatePassword($length = 10)
    {
        // str_shuffle gives one in all possible permutations of the shuffled string
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz-0123456789_ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#+=,;:.$'), 0, $length);
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof CasToken;
    }
}
