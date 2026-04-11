<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    // Étape 1 — Demande de réinitialisation
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function request(Request $request, UtilisateurRepository $repo, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $user  = $repo->findOneBy(['email' => $email]);

            if ($user) {
                $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $user->setTwoFactorSecret($code);
                $em->flush();

                $emailMsg = (new Email())
                    ->from(new Address('noreply@stratix.com', 'Stratix'))
                    ->to($email)
                    ->subject('Code de réinitialisation — Stratix')
                    ->html("
                        <div style='font-family:sans-serif;max-width:420px;margin:0 auto;padding:2rem;'>
                            <h2 style='color:#2196f3;'>Réinitialisation du mot de passe</h2>
                            <p>Bonjour <strong>{$user->getPrenom()} {$user->getNom()}</strong>,</p>
                            <p>Votre code de réinitialisation est :</p>
                            <div style='font-size:2.5rem;font-weight:700;letter-spacing:.5rem;color:#1e1b4b;background:#f0f4ff;padding:1.5rem;border-radius:8px;text-align:center;margin:1rem 0;'>
                                {$code}
                            </div>
                            <p style='color:#9ca3af;font-size:.85rem;'>Ce code est valable 15 minutes. Si vous n'avez pas fait cette demande, ignorez cet email.</p>
                        </div>
                    ");

                $mailer->send($emailMsg);
            }

            // Même message que l'email existe ou non (sécurité)
            $request->getSession()->set('reset_email', $email);
            return $this->redirectToRoute('app_reset_verify_code');
        }

        return $this->render('auth/forgot_password.html.twig', ['error' => $error]);
    }

    // Étape 2 — Vérification du code
    #[Route('/reset-verify-code', name: 'app_reset_verify_code', methods: ['GET', 'POST'])]
    public function verifyCode(Request $request, UtilisateurRepository $repo, EntityManagerInterface $em): Response
    {
        $email = $request->getSession()->get('reset_email');
        if (!$email) {
            return $this->redirectToRoute('app_forgot_password');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $code = trim($request->request->get('code', ''));
            $user = $repo->findOneBy(['email' => $email]);

            if ($user && $user->getTwoFactorSecret() === $code) {
                $request->getSession()->set('reset_verified', true);
                return $this->redirectToRoute('app_reset_new_password');
            }

            $error = 'Code incorrect. Veuillez réessayer.';
        }

        return $this->render('auth/reset_verify_code.html.twig', [
            'error' => $error,
            'email' => $email,
        ]);
    }

    // Étape 3 — Nouveau mot de passe
    #[Route('/reset-new-password', name: 'app_reset_new_password', methods: ['GET', 'POST'])]
    public function newPassword(Request $request, UtilisateurRepository $repo, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $email    = $request->getSession()->get('reset_email');
        $verified = $request->getSession()->get('reset_verified');

        if (!$email || !$verified) {
            return $this->redirectToRoute('app_forgot_password');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (strlen($password) < 8) $errors['password'] = 'Minimum 8 caractères.';
            elseif (!preg_match('/[A-Z]/', $password)) $errors['password'] = 'Au moins une majuscule.';
            elseif (!preg_match('/[0-9]/', $password)) $errors['password'] = 'Au moins un chiffre.';

            if ($password && $password !== $confirm) $errors['confirm'] = 'Les mots de passe ne correspondent pas.';

            if (empty($errors)) {
                $user = $repo->findOneBy(['email' => $email]);
                $user->setPassword($hasher->hashPassword($user, $password));
                $user->setTwoFactorSecret(null);
                $em->flush();

                $request->getSession()->remove('reset_email');
                $request->getSession()->remove('reset_verified');

                $this->addFlash('success', 'Mot de passe modifié avec succès !');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/reset_new_password.html.twig', ['errors' => $errors]);
    }
}
