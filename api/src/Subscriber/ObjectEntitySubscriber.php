<?php


namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Component;
use App\Entity\ObjectEntity;
use App\Service\ObjectEntityService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class ObjectEntitySubscriber implements EventSubscriberInterface
{
    private $params;
    private $commonGroundService;
    private $objectEntityService;

    public function __construct(ParameterBagInterface $params, CommongroundService $commonGroundService, ObjectEntityService $objectEntityService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->objectEntityService = $objectEntityService;
    }

    public static function getSubscribedEvents()
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

        $componentCode = $event->getRequest()->attributes->get("component");
        $entityName = $event->getRequest()->attributes->get("entity");
        $uuid = $event->getRequest()->attributes->get("uuid");

        if ($resource instanceof ObjectEntity) {
            if ($route == 'api_object_entities_post_objectentity_collection') {
                $this->objectEntityService->handlePost($resource);
            } elseif ($route == 'api_object_entities_get_objectentity_collection') {
                $this->objectEntityService->handleGet($resource);
            }
        }
    }
}
