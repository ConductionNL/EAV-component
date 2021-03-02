<?php


namespace App\Subscriber;

use App\Entity\Attribute;
use Conduction\CommonGroundBundle\Service\CommonGroundService;

class AttributeSubscriber
{
    private $params;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommongroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public static function getAttributes()
    {
        return [
            KernelEvents::VIEW => ['attribute', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function attribute(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $contentType = $event->getRequest()->headers->get('accept');
        $route = $event->getRequest()->attributes->get('_route');
        $resource = $event->getControllerResult();

        var_dump($method);
    }
}
