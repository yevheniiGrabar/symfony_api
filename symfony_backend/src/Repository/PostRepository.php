<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends AbstractRepository
{
    /** @param ManagerRegistry $registry */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    /**
     * @return string
     */
    protected function getModel(): string
    {
        return Post::class;
    }
}
