<?php

namespace App\Repository;

use App\Entity\TodoListMatchDeveloper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TodoListMatchDeveloper|null find($id, $lockMode = null, $lockVersion = null)
 * @method TodoListMatchDeveloper|null findOneBy(array $criteria, array $orderBy = null)
 * @method TodoListMatchDeveloper[]    findAll()
 * @method TodoListMatchDeveloper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TodoListMatchDeveloperRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoListMatchDeveloper::class);
    }

    public function removeAllTodo()
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
