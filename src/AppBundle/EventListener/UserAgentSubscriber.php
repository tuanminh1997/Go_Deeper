<?php


namespace AppBundle\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

class UserAgentSubscriber implements EventSubscriberInterface
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function onKernelRequest(GetResponseEvent $event )
    {
        if(!$event->isMasterRequest()){
            return;
        }

        $request=$event->getRequest();
        $userAgent=$request->headers->get('User-Agent');

        $this->logger->info('Hello there browser: '.$userAgent);

        if(rand(0,100)>50){
//            $reponse=new Response('Come back later');
//            $event->setResponse($reponse);
        }

        $isMac= strpos($userAgent,'mac') !==false;
        $request->attributes->set('isMac',$isMac);
        if($request->query->get('notMac')){
            $isMac =false;
        }
        $request->attributes->set('isMac',$isMac);

   /*     $request->attributes->set('_controller',function ($id){
            return new Response('Hello '.$id);

        });*/
    }
    public static function getSubscribedEvents()
    {
        return array(
            //kernel.request
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }


}