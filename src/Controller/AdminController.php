<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(UtilisateurRepository $repo): Response
    {
        $stats = [
            'total'  => $repo->count([]),
            'actifs' => $repo->count(['statut' => 'actif']),
            'locked' => $repo->count(['account_locked' => true]),
            'admins' => $repo->count(['role' => 'admin']),
        ];

        $recent = $repo->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'stats'  => $stats,
            'recent' => $recent,
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UtilisateurRepository $repo, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $role   = $request->query->get('role', '');
        $statut = $request->query->get('statut', '');

        $qb = $repo->createQueryBuilder('u');
        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR u.email LIKE :s')
               ->setParameter('s', "%$search%");
        }
        if ($role)   { $qb->andWhere('u.role = :role')->setParameter('role', $role); }
        if ($statut) { $qb->andWhere('u.statut = :statut')->setParameter('statut', $statut); }

        $users = $qb->orderBy('u.id', 'DESC')->getQuery()->getResult();

        return $this->render('admin/users.html.twig', [
            'users'  => $users,
            'search' => $search,
            'role'   => $role,
            'statut' => $statut,
        ]);
    }

    #[Route('/users/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $user->setDateAjout(new \DateTime());
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur créé.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', ['form' => $form, 'title' => 'Nouvel utilisateur']);
    }

    #[Route('/users/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(Utilisateur $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UtilisateurType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', ['form' => $form, 'title' => 'Modifier l\'utilisateur']);
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Utilisateur $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/toggle-lock', name: 'admin_user_toggle_lock', methods: ['POST'])]
    public function toggleLock(Utilisateur $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->request->get('_token'))) {
            $user->setAccountLocked(!$user->isAccountLocked());
            if (!$user->isAccountLocked()) {
                $user->setFailedLoginAttempts(0);
                $user->setLockedUntil(null);
            }
            $em->flush();
            $this->addFlash('success', 'Statut du compte mis à jour.');
        }
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/change-role', name: 'admin_user_change_role', methods: ['POST'])]
    public function changeRole(Utilisateur $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('role'.$user->getId(), $request->request->get('_token'))) {
            $role = $request->request->get('role');
            if (in_array($role, ['admin', 'employe', 'responsable_rh', 'responsable_projet', 'responsable_production', 'ceo'])) {
                $user->setRole($role);
                $em->flush();
                $this->addFlash('success', 'Rôle mis à jour.');
            }
        }
        return $this->redirectToRoute('admin_users');
    }
}
