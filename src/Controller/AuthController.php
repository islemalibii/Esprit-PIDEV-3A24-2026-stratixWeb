<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/', name: 'resp_event_index')]
    public function home(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_dashboard');
        }
        return $this->render('admin/events/responsableEvent.html.twig', ['user' => $user]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('resp_event_index');
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error'         => $authUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin/service/index.html.twig');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $nom      = trim($request->request->get('nom', ''));
            $prenom   = trim($request->request->get('prenom', ''));
            $email    = trim($request->request->get('email', ''));
            $cin      = trim($request->request->get('cin', ''));
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (!$nom)    $errors['nom']    = 'Le nom est obligatoire.';
            elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $nom)) $errors['nom'] = 'Lettres uniquement.';

            if (!$prenom) $errors['prenom'] = 'Le prénom est obligatoire.';
            elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $prenom)) $errors['prenom'] = 'Lettres uniquement.';

            if (!$email)  $errors['email']  = 'L\'email est obligatoire.';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email invalide.';
            elseif ($em->getRepository(Utilisateur::class)->findOneBy(['email' => $email])) $errors['email'] = 'Cet email est déjà utilisé.';

            if (!$cin)    $errors['cin']    = 'Le CIN est obligatoire.';
            elseif (!preg_match('/^\d{8}$/', $cin)) $errors['cin'] = 'Le CIN doit contenir exactement 8 chiffres.';

            if (!$password) $errors['password'] = 'Le mot de passe est obligatoire.';
            elseif (strlen($password) < 8) $errors['password'] = 'Minimum 8 caractères.';
            elseif (!preg_match('/[A-Z]/', $password)) $errors['password'] = 'Au moins une majuscule requise.';
            elseif (!preg_match('/[0-9]/', $password)) $errors['password'] = 'Au moins un chiffre requis.';

            if ($password && $password !== $confirm) $errors['confirm'] = 'Les mots de passe ne correspondent pas.';

            if (empty($errors)) {
                $user = new Utilisateur();
                $user->setNom($nom)
                     ->setPrenom($prenom)
                     ->setEmail($email)
                     ->setCin((int)$cin)
                     ->setRole('EMPLOYE')
                     ->setStatut('actif')
                     ->setDateAjout(new \DateTime())
                     ->setPassword($hasher->hashPassword($user, $password));

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Compte créé ! Vous pouvez vous connecter.');
                return $this->redirectToRoute('admin/service/index.html.twig');
            }
        }

        return $this->render('auth/signup.html.twig', [
            'errors' => $errors,
            'old'    => $request->request->all(),
        ]);
    }
}
