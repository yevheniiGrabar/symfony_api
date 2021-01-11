<?php

namespace App\Repository;

use App\Entity\EntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getModel());
    }

    /**
     * @param $entity
     */
    public function plush(EntityInterface $entity): void
    {
        $manager = $this->getEntityManager();
        $manager->persist($entity);
        $manager->flush();
    }

    /**
     * @param EntityInterface $entity
     * @return bool
     */
    public function delete(EntityInterface $entity): bool
    {
        $manager = $this->getEntityManager();

        try {
            $manager->remove($entity);
        } catch (ORMException $e) {
            return false;
        }

        try {
            $manager->flush();
        } catch (OptimisticLockException | ORMException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    abstract protected function getModel(): string;
}
