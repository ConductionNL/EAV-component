<?php


namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Component;
use App\Entity\ObjectCommunication;
use App\Entity\ObjectEntity;
use App\Service\NotificationService;
use App\Service\ObjectEntityService;
use App\Service\ObjectService;
use App\Service\ValidationService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use function GuzzleHttp\json_decode;

class ObjectSubscriber implements EventSubscriberInterface
{
    private $em;
    private $params;
    private $commonGroundService;
    private ObjectService $objectService;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params, CommongroundService $commonGroundService, ObjectService $objectService, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->objectService = $objectService;
        $this->notificationService = $notificationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['objectEntity', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function objectEntity(ViewEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');
        $resource = $event->getControllerResult();

        if ($route == 'api_object_entities_post_objectentity_collection'
            || $route == 'api_object_entities_post_putobjectentity_collection'
            || $route == 'api_object_entities_get_objectentity_collection'
            || $route == 'api_object_entities_get_uriobjectentity_collection')
        {
            $componentCode = $event->getRequest()->attributes->get("component");
            $entityName = $event->getRequest()->attributes->get("entity");
            $uuid = $event->getRequest()->attributes->get("uuid");

            if ($route == 'api_object_entities_post_objectentity_collection'
                || $route == 'api_object_entities_post_putobjectentity_collection') {
                $body = json_decode($event->getRequest()->getContent(), true);
            } else {
                // get query params for get calls (put in $body to set these variables in the objectService, confusing name for this...)
                $body = [];
                if ($event->getRequest()->query->get('@self')) {
                    $body['@self'] = $event->getRequest()->query->get('@self');
                } elseif ($event->getRequest()->query->get('self')) {
                    $body['@self'] = $event->getRequest()->query->get('self');
                } elseif ($event->getRequest()->query->get('objectEntityId')) {
                    $uuid = $event->getRequest()->query->get('objectEntityId');
                }
            }
            $this->objectService->setEventVariables($body, $entityName, $uuid, $componentCode);
            $notificationTopic = $componentCode . '/' . $entityName;

            // TODO: COPY PASTE OLD CODE^ (might change, but lets use this for now)


            // TODO: NEW CODE ...

            // Validate the post data for this entity
            if ($route == 'api_object_entities_post_objectentity_collection' && $resource instanceof ObjectEntity) {
                // Make sure we do not create and leave behind empty ObjectEntity objects
                $this->em->remove($resource);
                $this->em->flush();

                $result = $this->objectService->handlePost($resource);
            } else {
                $result = 'stop for now';
            }

            $responseType = Response::HTTP_CREATED;
            $response = new Response(
                json_encode($result),
                $responseType,
                ['content-type' => 'application/json']
            );
            $event->setResponse($response);


            // TODO: OLD COPY PASTE CODE FROM ObjectEntityservice... (here for easy re-use if needed, could also just be removed!)

            // oldTODO: post_objectentity and post_putobjectentity should use the same 'handlePost' function (this should make this code look a lot cleaner as well)
            if ($route == 'api_object_entities_post_objectentity_collection' && $resource instanceof ObjectEntity) {
                // Check if we actually need to do / are doing a put. If @self has already an objectEntity object in EAV
                if (isset($body['@self'])) {
                    // Get existing objectEntity with @self
                    $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['uri' => $body['@self']]);
                    if (!empty($object)) {
                        $this->em->remove($resource);
                        $this->em->flush();

                        $result = $this->objectService->handlePut();
                        $notificationAction = 'Update';
                        $responseType = Response::HTTP_CREATED;
                    }
                }

                // If not, we are just doing a post
                if (!isset($result)) {
                    try {
                        $result = $this->objectService->handlePost($resource);
                        if (isset($body['@self'])) {
                            $notificationAction = 'Update';
                        } else {
                            $notificationAction = 'Create';
                        }
                        $responseType = Response::HTTP_CREATED;
                    } catch (HttpException $e) {
                        // Lets not create a new ObjectEntity when we get an error!
                        $this->em->remove($resource);
                        $this->em->flush();
                        throw new HttpException($e->getMessage(), 400);
                    }
                }
            } elseif ($route == 'api_object_entities_post_putobjectentity_collection' && $resource instanceof ObjectEntity) {
                // Lets not create a new ObjectEntity every time we do a put!
                $this->em->remove($resource);
                $this->em->flush();

                $result = $this->objectService->handlePut();
                $notificationAction = 'Update';
                $responseType = Response::HTTP_CREATED;
            } elseif ($route == 'api_object_entities_get_objectentity_collection' || $route == 'api_object_entities_get_uriobjectentity_collection') {
                if($uuid || (isset($body['@self']) && $body['@self'])){
                    $result = $this->objectService->handleGet();
                } else {
                    $result['@type'] = 'hydra:Collection';
                    $result['hydra:member'] = $this->objectService->handleGetCollection();
                    $result['hydra:totalItems'] = count($result['hydra:member']);
                }
                $responseType = Response::HTTP_OK;
            }

            if (isset($notificationTopic) && isset($notificationAction) && isset($result['@id'])) {
                $this->notificationService->notify($notificationTopic, $notificationAction, $result['@id']);
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
