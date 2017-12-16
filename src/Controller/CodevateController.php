<?php
namespace App\Controller;

use App\Entity\SMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class CodevateController extends Controller
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
     * Function index to return 'hi_codevate' view
     *
     * @return void
     */
    public function hi_codevate()
    {
        return $this->render('codevate/hi_codevate.html.twig');
    }

    /**
     * Function sms_form rendering the form
     *
     * @param Request $request
     * @return void
     */
    public function sms_form(Request $request)
    {
        $sms_fields = new SMS();
        $sms_fields->setNumber('0');
        $sms_fields->setMessage('Write a text message');

        $form = $this->createFormBuilder($sms_fields)
            ->add('number', NumberType::class)
            ->add('message', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Send SMS'))
            ->getForm();

        return $this->render('codevate/sms_form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}