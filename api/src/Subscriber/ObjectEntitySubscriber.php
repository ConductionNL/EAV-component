<?php


namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Component;
use App\Entity\ObjectEntity;
use App\Service\ObjectEntityService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ObjectEntitySubscriber implements EventSubscriberInterface
{
    private $em;
    private $params;
    private $commonGroundService;
    private $objectEntityService;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params, CommongroundService $commonGroundService, ObjectEntityService $objectEntityService)
    {
        $this->em = $em;
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

        if ($route == 'api_object_entities_post_objectentity_collection'
            || $route == 'api_object_entities_post_putobjectentity_collection'
            || $route == 'api_object_entities_get_objectentity_collection')
        {
            $componentCode = $event->getRequest()->attributes->get("component");
            $entityName = $event->getRequest()->attributes->get("entity");
            $uuid = $event->getRequest()->attributes->get("uuid");
            $body = json_decode($event->getRequest()->getContent(), true);

            $this->objectEntityService->setEventVariables($componentCode, $entityName, $uuid, $body);

            //TODO: post_objectentity and post_putobjectentity should use the same 'handlePost' function
            if ($route == 'api_object_entities_post_objectentity_collection' && $resource instanceof ObjectEntity) {
                try {
                    $result = $this->objectEntityService->handlePost($resource);
                    $responseType = Response::HTTP_CREATED;
                } catch (HttpException $e) {
                    $this->em->remove($resource);
                    $this->em->flush();
                    throw new HttpException($e->getMessage(), 400);
                }
            } elseif ($route == 'api_object_entities_post_putobjectentity_collection' && $resource instanceof ObjectEntity) {
                $result = $this->objectEntityService->handlePut($resource);
                $responseType = Response::HTTP_CREATED;
            } elseif ($route == 'api_object_entities_get_objectentity_collection') {
                $result = $this->objectEntityService->handleGet();
                $responseType = Response::HTTP_OK;
            }

            $response = new Response(
                json_encode($result),
                $responseType,
                ['content-type' => 'application/json']
            );
            $event->setResponse($response);
        }
    }
}
