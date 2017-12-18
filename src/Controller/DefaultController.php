<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Function index to return homepage view
     *
     * @return void
     */
    public function index()
    {
        return $this->render('base.html.twig');
    }
    
    /**
     * Function admin redirect to admin page
     *
     * @return void
     */
    public function admin()
    {
        return $this->render('admin.twig', array(
            'form' => $form->createView(),
            'test'  =>  $test,
        ));
    }
}