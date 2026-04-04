<?php

namespace App\Repository;

use App\Entity\Projet;
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
            ->orderBy('p.id', 'DESC') // Les plus récents en premier
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
}