<?php

namespace App\Repository;

use Doctrine\ORM\ORMException;
use App\Entity\EntityInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getModel());
    }

    /**
     * @param EntityInterface $entity
     * @throws ORMException
     * @throws OptimisticLockException
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

    /** @return string */
    abstract protected function getModel(): string;
}

