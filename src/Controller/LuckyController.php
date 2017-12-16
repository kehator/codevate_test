<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller; 


class LuckyController extends Controller
{
    /**
     * Function number generates random number (symfony tutorial)
     *
     * @return void
     */
    public function number()
    {
        $number = mt_rand(0, 100);

        return $this->render('lucky/number.html.twig', array(
            'number' => $number,
        ));
    }
}