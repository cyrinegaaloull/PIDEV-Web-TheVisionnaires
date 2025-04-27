<?php
namespace App\Security;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Entity\Roles;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry         $clientRegistry;
    private EntityManagerInterface $em;
    private RouterInterface        $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em             = $em;
        $this->router         = $router;
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client     = $this->clientRegistry->getClient('google');
        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUser();          // exchanges code for user info

        $email = $googleUser->getEmail();

        return new SelfValidatingPassport(
            new UserBadge($email, function($userIdentifier) use ($googleUser) {
                $repoUser = $this->em->getRepository(Users::class);
                $repoRole = $this->em->getRepository(Roles::class);
        
                $user = $repoUser->findOneBy(['email' => $userIdentifier]);
        
                if (!$user) {
                    // fetch the default "USER" role by its `role` column
                    $defaultRole = $repoRole->findOneBy(['role' => 'USER']);
                    if (!$defaultRole) {
                        throw new \RuntimeException('Default role not found – make sure you have a row where role = "user"');
                    }
        
                    $user = (new Users())
                        ->setEmail($googleUser->getEmail())
                        ->setUsername($googleUser->getName() ?? $googleUser->getNickname())
                        ->setAvatar($googleUser->getAvatar())
                        ->setNom('')
                        ->setPrenom('')
                        ->setPassword(bin2hex(random_bytes(10)))
                        ->setRole($defaultRole);
        
                    $this->em->persist($user);
                    $this->em->flush();
                }
        
                return $user;
            })
        );
        
          
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        // redirect to intended URL or homepage
        $target = $this->router->generate('app_home');
        return new RedirectResponse($target);
    }

    public function onAuthenticationFailure(Request $request, \Throwable $exception): ?RedirectResponse
    {
        // handle failure (e.g. flash an error & redirect)
        return new RedirectResponse($this->router->generate('app_login'));
    }

    protected function getClientRegistry(): ClientRegistry
    {
        return $this->clientRegistry;
    }
}
