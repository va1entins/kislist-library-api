<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'books')]
class Book
{
    // Numer seryjny książki — 6-cyfrowy, wprowadzany ręcznie przez operatora (nie autoincrement)
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $author;

    #[ORM\Column(type: 'boolean')]
    private bool $isBorrowed = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $borrowedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $borrowerCardNumber = null;

    public function __construct(int $id, string $title, string $author)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function isBorrowed(): bool
    {
        return $this->isBorrowed;
    }

    public function setIsBorrowed(bool $isBorrowed): void
    {
        $this->isBorrowed = $isBorrowed;
    }

    public function getBorrowedAt(): ?\DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(?\DateTimeImmutable $borrowedAt): void
    {
        $this->borrowedAt = $borrowedAt;
    }

    public function getBorrowerCardNumber(): ?int
    {
        return $this->borrowerCardNumber;
    }

    public function setBorrowerCardNumber(?int $borrowerCardNumber): void
    {
        $this->borrowerCardNumber = $borrowerCardNumber;
    }
}
