<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function save(Book $book, bool $flush = true): void
    {
        $this->getEntityManager()->persist($book);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $book, bool $flush = true): void
    {
        $this->getEntityManager()->remove($book);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Book[]
     */
    public function findAllBooks(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }
}
