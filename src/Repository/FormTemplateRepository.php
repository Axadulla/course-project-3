<?php

namespace App\Repository;

use App\Entity\FormTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;


/**
 * @extends ServiceEntityRepository<FormTemplate>
 */
class FormTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormTemplate::class);
    }


    public function searchByTitle(string $query, User $user, bool $isSuperAdmin = false): array
    {
        $qb = $this->createQueryBuilder('f')
            ->where('LOWER(f.title) LIKE :query')
            ->setParameter('query', '%' . mb_strtolower($query) . '%');

        if (!$isSuperAdmin) {
            $qb->andWhere('f.owner = :user OR f.isPublic = true')
                ->setParameter('user', $user);
        }

        return $qb->orderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult();
    }



}
