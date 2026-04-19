<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Facture;
use App\Repository\FactureRepository;

class NumberFactureGenerator
{

    public function __construct() {}

    public function generateNumber(FactureRepository $fr)
    {
        $maxId = $fr->findMaxId();
        $year = date('Y');

        return 'INV-' . $maxId . '-' . $year;
    }
}
