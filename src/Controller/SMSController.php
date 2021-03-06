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
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Cache\Adapter\RedisAdapter;


class SMSController extends Controller
{
    public function __construct()
    {
        $this->redis = RedisAdapter::createConnection(
            'redis://localhost:6379',
            array(
                'persistent' => 0,
                'persistent_id' => null,
                'timeout' => 30,
                'read_timeout' => 0,
                'retry_interval' => 0,
            )
        );

        $this->cache = new RedisAdapter(
            $this->redis,
            $namespace = '',
            $defaultLifetime = 3600
        );
    }
    /**
     * Function index to return 'account' view
     *
     * @return void
     */
    public function account()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('sms/app.twig');
    }

    /**
     * Function save to persist data in DB
     *
     * @param mixed $data
     * @return void
     */
    protected function save($data) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();

        return true;
    }

    /**
     * Function smsForm rendering the form
     *
     * @param Request $request
     * @return void
     */
    public function smsForm(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $user_id = $user->getID();        
        $sms_timelimit_message = null;
        
        // check if timelimit reached and add message if so (if message exist, it will prevent user from sending an sms)
        $timelimit = $this->redis->get('sms_time_limit_reached_user_'.$user_id);
        if ($timelimit) {
            $sms_timelimit_message = 'Please wait 15sec before sending next SMS.';
        }

        $sms_fields = new SMS();
        $sms_fields->setNumber(null);
        $sms_fields->setMessage(null);
        $sms_fields->setUserID($user_id);
        $sms_fields->setStatus('new');
        $sms_fields->setCreated(new \DateTime("now"));

        $form = $this->createFormBuilder($sms_fields)
                        ->add('number', TextType::class, array(
                            'constraints' => array(
                                new Regex(array(
                                    'pattern' => "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/",
                                    'match'   => true,
                                    'message' => 'This isn\'t a valid mobile phone number.',
                                )),
                            ),
                            'attr'  =>  array(
                                'class' =>  'form-control col-xs-12 col-md-6 col-lg-4',
                            ),
                        ))
                        ->add('message', TextareaType::class, array(
                            'constraints' => array(
                                new NotBlank(),
                                new Length(array('max' => 140)),
                            ),
                            'attr'  =>  array(
                                'class' =>  'form-control col-xs-12 col-md-6 col-lg-4',
                            ),
                        ))
                        ->add('save', SubmitType::class, array(
                                'label' => 'Send SMS', 
                                'attr'  =>  array(
                                    'class' =>  'btn btn-secondary mt-3',            // return $this->render('sms/sms_form.twig', array(
                                        //     'sms_timelimit_message' => $sms_timelimit_message,
                                        // ));
                                    'type'  =>  'submit',
                                ),
                        ))
                        ->getForm();
        

        $form->handleRequest($request);

        $SMS = $form->getData();

        // check if form is submitted and valid, and send sms if so.
        if ($form->isSubmitted() && $form->isValid()) {  
            return $this->sendSMS($SMS);
        }

        return $this->render('sms/sms_form.twig', array(
            'form' => $form->createView(),
            'sms_timelimit_message' => $sms_timelimit_message,
        ));
    }

    /**
     * Function smsHistory to show all sent sms
     *
     * @param mixed $user_id
     * @return void
     */
    public function smsHistory($user_id = null) {
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
     * Function sendSMS to send SMS
     *
     * @param mixed $SMS
     * @return void
     */
    protected function sendSMS($SMS)
    {
        $user = $this->getUser();
        $user_id = $user->getID();
        $timelimit_key = 'sms_time_limit_reached_user_'.$user_id;

        $sms_sent_message = 'You have sent the SMS to number: '. $SMS->getNumber();
        $sms_fail_message = 'Failed. Unfortunately you haven\'t sent the SMS to number: '. $SMS->getNumber();

        $SMS->setUser($user);        
        $SMS->setStatus('queued');
        $save = $this->save($SMS);

        if ($save) {
            $queueing = $this->queueSMS($SMS->getMessage());

            if ($queueing == false) {
                $SMS->setStatus('fail');
                $save = $this->save($SMS);

                return $this->render('sms/sms_sent.twig', array(
                    'sms_sent_message'  =>  $sms_fail_message,
                ));
            }
        }

        $SMS->setStatus('sent');
        $save = $this->save($SMS);

        $this->redis->set($timelimit_key, 'please wait 15sec');
        $this->redis->expireat($timelimit_key, time() + 15);

        return $this->render('sms/sms_sent.twig', array(
            'sms_sent_message'  =>  $sms_sent_message,
        ));
    }

    /**
     * Function queueSMS to put message into the queue system
     *
     * @param mixed $msg
     * @return void
     */
    protected function queueSMS($msg)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('sms_queue', false, false, false, false);

        $msg = new AMQPMessage($msg);
        $channel->basic_publish($msg, '', 'sms_queue');
        // echo " [x] Sent ", $msg->body, "\n";
        
        $channel->close();
        $connection->close();

        return true;
    }
}