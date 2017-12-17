<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    
    /**
     * Function admin redirect to admin page
     *
     * @return void
     */
    public function admin()
    {
        return $this->render('codevate/admin.html.twig', array(
            'form' => $form->createView(),
            'test'  =>  $test,
        ));
    }
}