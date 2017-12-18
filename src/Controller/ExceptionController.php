<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExceptionController extends Controller
{
    
    /**
     * Function showException redirect to the error page
     *
     * @return void
     */
    public function showException()
    {
        // showAction();
        // findTemplate();
        return $this->render('Exception/error404.twig');
    }

    /**
     * Function showException403 redirect to the error403 page
     *
     * @return void
     */
    public function showException403()
    {
        // showAction();
        // findTemplate();
        return $this->render('Exception/error403.twig');
    }
}