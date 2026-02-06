<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[]
     */
    public function findPublishedOrderByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.author', 'a')
            ->addSelect('a')
            ->innerJoin('p.category', 'c')
            ->addSelect('c')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWithRelations(?int $id): ?Post
    {
        if ($id === null) {
            return null;
        }
        return $this->createQueryBuilder('p')
            ->innerJoin('p.author', 'a')
            ->addSelect('a')
            ->innerJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('p.comments', 'com')
            ->addSelect('com')
            ->leftJoin('com.author', 'ca')
            ->addSelect('ca')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
