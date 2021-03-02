<?php


namespace App\Subscriber;

use App\Entity\Attribute;
use Conduction\CommonGroundBundle\Service\CommonGroundService;

class ObjectSubscriber
{
    private $params;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommongroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public static function getObjectEntity()
    {
        return [
            KernelEvents::VIEW => ['objectEntity', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function objectEntity(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $contentType = $event->getRequest()->headers->get('accept');
        $route = $event->getRequest()->attributes->get('_route');
        $resource = $event->getControllerResult();

        var_dump($method);
    }
}
