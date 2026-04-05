<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em,
    ) {}

    public function authenticate(Request $request): Passport
    {
        $email    = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $token    = $request->request->get('_csrf_token', '');

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($user) {
            // Vérifie si le compte est verrouillé
            if ($user->isAccountLocked()) {
                $until = $user->getLockedUntil();
                if ($until && $until > new \DateTime()) {
                    throw new CustomUserMessageAuthenticationException(
                        'Compte verrouillé jusqu\'au ' . $until->format('d/m/Y H:i') . '.'
                    );
                }
                // Déverrouillage automatique si délai expiré
                $user->setAccountLocked(false);
                $user->setFailedLoginAttempts(0);
                $this->em->flush();
            }
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('authenticate', $token)]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var Utilisateur $user */
        $user = $token->getUser();
        $user->setFailedLoginAttempts(0);
        $this->em->flush();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        // Responsables → tâches directement
        $role = $user->getRole();
        if (in_array($role, ['responsable_rh', 'responsable_projet', 'responsable_production', 'ceo'])) {
            return new RedirectResponse($this->router->generate('app_tache_index'));
        }

        // Employés → espace employé
        return new RedirectResponse($this->router->generate('app_employee_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $email = $request->request->get('email', '');
        $user  = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($user && !($exception instanceof CustomUserMessageAuthenticationException)) {
            $attempts = ($user->getFailedLoginAttempts() ?? 0) + 1;
            $user->setFailedLoginAttempts($attempts);

            if ($attempts >= 5) {
                $user->setAccountLocked(true);
                $user->setLockedUntil((new \DateTime())->modify('+30 minutes'));
            }
            $this->em->flush();
        }

        $request->getSession()->set('_security.last_error', $exception);
        $request->getSession()->set('_security.last_username', $email);

        return new RedirectResponse($this->router->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }
}
