<?php

namespace App\Repository;

use App\Entity\Sprint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sprint>
 */
class SprintRepository extends ServiceEntityRepository
{
   

    public function __construct(ManagerRegistry $registry)
    {
        // Ajoute "::class" après Sprint
        parent::__construct($registry, Sprint::class); 
    }

    // Tu pourras ajouter ici des méthodes personnalisées plus tard
    // (ex: trouver les sprints d'un projet spécifique)
}