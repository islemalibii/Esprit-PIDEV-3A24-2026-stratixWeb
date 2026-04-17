<?php

namespace App\Twig;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class NotificationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private UtilisateurRepository $repo,
        private RequestStack $requestStack
    ) {}

    public function getGlobals(): array
    {
        $notifications = [];

        try {
            $session = $this->requestStack->getSession();
            /** @var \DateTime|null $readAt */
            $readAt = $session->get('notif_read_at');

            $newUsers = $this->repo->findBy([], ['id' => 'DESC'], 5);
            foreach ($newUsers as $u) {
                $date = $u->getDateAjout();
                // Filtrer si déjà lu
                if ($readAt && $date && $date <= $readAt) continue;
                $notifications[] = [
                    'title'   => 'Nouvel utilisateur : ' . $u->getPrenom() . ' ' . $u->getNom(),
                    'message' => $u->getEmail(),
                    'date'    => $date,
                    'color'   => '#4f46e5',
                    'icon'    => 'ti-user-plus',
                ];
            }

            $locked = $this->repo->findBy(['account_locked' => true], ['id' => 'DESC'], 5);
            foreach ($locked as $u) {
                $date = $u->getLockedUntil();
                if ($readAt && (!$date || $date <= $readAt)) continue;
                $notifications[] = [
                    'title'   => 'Compte verrouillé',
                    'message' => $u->getPrenom() . ' ' . $u->getNom() . ' (' . ($u->getFailedLoginAttempts() ?? 0) . ' tentatives)',
                    'date'    => $date,
                    'color'   => '#dc2626',
                    'icon'    => 'ti-lock',
                ];
            }
        } catch (\Exception $e) {}

        return ['admin_notifications' => $notifications];
    }
}
