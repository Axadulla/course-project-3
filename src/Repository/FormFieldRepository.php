<?php
namespace App\Repository;

use App\Entity\FormField;
use App\Entity\FormTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormField>
 */
class FormFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormField::class);
    }

    public function findByTemplateOrdered(FormTemplate $template): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.formTemplate = :template')
            ->setParameter('template', $template)
            ->orderBy('f.order', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
