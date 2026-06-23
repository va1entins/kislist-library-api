<?php

declare(strict_types=1);

namespace App\Exception;

class BookNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Książka o numerze seryjnym %d nie została znaleziona', $id));
    }
}
