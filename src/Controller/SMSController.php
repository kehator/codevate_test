<?php
namespace App\Controller;

use App\Form\SMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;


class SMSController extends Controller
{
    /**
     * Function index to return 'say_hi' view
     *
     * @return void
     */
    public function say_hi()
    {
        return $this->render('sms/app.twig');
    }

    /**
     * Function sms_form rendering the form
     *
     * @param Request $request
     * @return void
     */
    public function sms_form(Request $request)
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // $user = $this->getUser();
        // $firstname = $user->getFirstName();

        $sms_fields = new SMS();
        $sms_fields->setNumber(null);
        $sms_fields->setMessage(null);

        $form = $this->createFormBuilder($sms_fields)
            ->add('number', TextType::class, array(
                'constraints' => array(
                    new Regex(array(
                        'pattern' => "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/",
                        'match'   => true,
                        'message' => 'This isn\'t a valid mobile phone number.',
                    )),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 140)),
                ),
            ))
            ->add('save', SubmitType::class, array('label' => 'Send SMS'))
            ->getForm();
        
        $test = 'form not submited yet';        

        $form->handleRequest($request);        

        // $message = $form->getData()->getMessage();

        $errors = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $test = 'form submited !';
        }

        return $this->render('sms/sms_form.twig', array(
            'form' => $form->createView(),
            'test'  =>  $test,
        ));
    }
}