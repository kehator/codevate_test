<?php
namespace App\Controller;

use App\Entity\SMS;
use App\Entity\User;
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
use Symfony\Component\HttpFoundation\Response;


class SMSController extends Controller
{
    /**
     * Function index to return 'account' view
     *
     * @return void
     */
    public function account()
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $user_id = $user->getID();

        $sms_fields = new SMS();
        $sms_fields->setNumber(null);
        $sms_fields->setMessage(null);
        $sms_fields->setUserID($user_id);
        $sms_fields->setStatus('queued');
        $sms_fields->setCreated(new \DateTime("now"));
        
        $sms_sent_message = null;

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
        

        $form->handleRequest($request);

        $SMS = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($this->save_sms($SMS)) {
                $sms_sent_message = 'You have sent the SMS to number: '. $SMS->getNumber();

                return $this->render('sms/sms_sent.twig', array(
                    'sms_sent_message'  =>  $sms_sent_message,
                ));
            }
        }

        return $this->render('sms/sms_form.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function sms_history($user_id = null) {

        if ($user_id === null) {
            $user_id = $this->getUser()->getID();
        }

        $user = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->find($user_id);

        $messages = $user->getMessages();
        
        return $this->render('sms/sms_history.twig', array(
            'username' => $user->getUsername(),
            'messages' => $messages,
        ));
    }


    /**
     * Function save_sms to persist message in DB
     *
     * @param mixed $SMS
     * @return void
     */
    protected function save_sms($SMS)
    {
        $user = $this->getUser();
        $SMS->setUser($user);        
        $SMS->setStatus('sent');

        $em = $this->getDoctrine()->getManager();
        $em->persist($SMS);
        $em->flush();


        return true;
    }
}