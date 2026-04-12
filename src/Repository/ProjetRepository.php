<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Utilisateur; 
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projet>
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * Récupère uniquement les projets actifs (non archivés)
     * @return Projet[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isArchived = :val')
            ->setParameter('val', false)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère uniquement les projets archivés
     * @return Projet[]
     */
    public function findAllArchived(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isArchived = :val')
            ->setParameter('val', true)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les projets où l'utilisateur est soit responsable, soit membre
     * @return Projet[]
     */
    public function findProjetsPourEmploye(Utilisateur $user): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.membres', 'm') 
            ->where('p.responsable = :user')
            ->orWhere('m = :user') 
            ->setParameter('user', $user)
            ->andWhere('p.isArchived = :archived') 
            ->setParameter('archived', false)
            ->orderBy('p.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

public function findActiveWithFilters($search, $statut)
{
    $qb = $this->createQueryBuilder('p')
        ->where('p.isArchived = :val')
        ->setParameter('val', false);

    if ($search) {
        $qb->andWhere('p.nom LIKE :search')
           ->setParameter('search', '%'.$search.'%');
    }

    if ($statut) {
        $qb->andWhere('p.statut = :statut')
           ->setParameter('statut', $statut);
    }

    // IMPORTANT : Retourne le QueryBuilder ou ->getQuery()
    return $qb->getQuery(); 
}
}