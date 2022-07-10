<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('deal', name: 'deal')]
class Controller extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/buy/{qty}', name: 'get', methods: [Request::METHOD_GET])]
    public function buy(int $qty): Response
    {
         $helper = new Helper();
        return $helper->buy($qty);
    }

}
