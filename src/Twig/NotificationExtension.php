<?php

namespace App\Twig;

use App\Repository\UtilisateurRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class NotificationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private UtilisateurRepository $repo) {}

    public function getGlobals(): array
    {
        $notifications = [];

        try {
            $newUsers = $this->repo->findBy([], ['id' => 'DESC'], 3);
            foreach ($newUsers as $u) {
                $notifications[] = [
                    'title'   => 'Nouvel utilisateur : ' . $u->getPrenom() . ' ' . $u->getNom(),
                    'message' => $u->getEmail(),
                    'date'    => $u->getDateAjout(),
                    'color'   => '#4f46e5',
                    'icon'    => 'ti-user-plus',
                ];
            }

            $locked = $this->repo->findBy(['account_locked' => true], ['id' => 'DESC'], 3);
            foreach ($locked as $u) {
                $notifications[] = [
                    'title'   => 'Compte verrouillé',
                    'message' => $u->getPrenom() . ' ' . $u->getNom() . ' (' . ($u->getFailedLoginAttempts() ?? 0) . ' tentatives)',
                    'date'    => $u->getLockedUntil(),
                    'color'   => '#dc2626',
                    'icon'    => 'ti-lock',
                ];
            }
        } catch (\Exception $e) {
            // DB pas encore disponible
        }

        return [
            'admin_notifications' => $notifications,
        ];
    }
}
