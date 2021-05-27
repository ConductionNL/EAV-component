<?php


namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Component;
use App\Entity\ObjectCommunication;
use App\Entity\ObjectEntity;
use App\Service\NotificationService;
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
use function GuzzleHttp\json_decode;

class ObjectEntitySubscriber implements EventSubscriberInterface
{
    private $em;
    private $params;
    private $commonGroundService;
    private $objectEntityService;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params, CommongroundService $commonGroundService, ObjectEntityService $objectEntityService, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->objectEntityService = $objectEntityService;
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
                $body = [];
                if ($event->getRequest()->query->get('@self')) {
                    $body['@self'] = $event->getRequest()->query->get('@self');
                } elseif ($event->getRequest()->query->get('self')) {
                    $body['@self'] = $event->getRequest()->query->get('self');
                } elseif ($event->getRequest()->query->get('objectEntityId')) {
                    $uuid = $event->getRequest()->query->get('objectEntityId');
                }
            }
            $this->objectEntityService->setEventVariables($body, $entityName, $uuid, $componentCode);
            $notificationTopic = $componentCode . '/' . $entityName;

            //TODO: post_objectentity and post_putobjectentity should use the same 'handlePost' function (this should make this code look a lot cleaner as well)
            if ($route == 'api_object_entities_post_objectentity_collection' && $resource instanceof ObjectEntity) {
                // Check if we actually need to do / are doing a put. If @self has already an objectEntity object in EAV
                if (isset($body['@self'])) {
                    // Get existing objectEntity with @self
                    $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['uri' => $body['@self']]);
                    if (!empty($object)) {
                        $this->em->remove($resource);
                        $this->em->flush();

                        $result = $this->objectEntityService->handlePut();
                        $notificationAction = 'Update';
                        $responseType = Response::HTTP_CREATED;
                    }
                }

                // If not, we are just doing a post
                if (!isset($result)) {
                    try {
                        $result = $this->objectEntityService->handlePost($resource);
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

                $result = $this->objectEntityService->handlePut();
                $notificationAction = 'Update';
                $responseType = Response::HTTP_CREATED;
            } elseif ($route == 'api_object_entities_get_objectentity_collection' || $route == 'api_object_entities_get_uriobjectentity_collection') {
                if($uuid || (isset($body['@self']) && $body['@self'])){
                    $result = $this->objectEntityService->handleGet();
                } else {
                    $result['@type'] = 'hydra:Collection';
                    $result['hydra:member'] = $this->objectEntityService->handleGetCollection();
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
        } elseif ($route == 'api_object_communications_post_collection'
            || $route == 'api_object_communications_get_collection')
        {
            $componentCode = 'eav';
            $uuid = null;
            if ($route == 'api_object_communications_post_collection') {
                $doGet = false;
                $body = json_decode($event->getRequest()->getContent(), true);

                if (isset($body['componentCode'])) {
                    $componentCode = $body['componentCode'];
                    unset($body['componentCode']);
                }
                if (isset($body['entityName'])) {
                    $entityName = $body['entityName'];
                    unset($body['entityName']);
                } else {
                    throw new HttpException('No entityName given!', 400);
                }
                if (isset($body['objectEntityId'])) {
                    $uuid = $body['objectEntityId'];
                    unset($body['objectEntityId']);
                }
                if (isset($body['self'])) {
                    $body['@self'] = $body['self'];
                    unset($body['self']);
                }
                if (isset($body['body'])) {
                    $body = array_merge($body, $body['body']);
                    unset($body['body']);
                }
                if (isset($body['doGet'])) {
                    $doGet = $body['doGet'];
                    unset($body['doGet']);
                }
            } else {
                if ($event->getRequest()->query->get('componentCode')) {
                    $componentCode = $event->getRequest()->query->get('componentCode');
                }
                if ($event->getRequest()->query->get('entityName')) {
                    $entityName = $event->getRequest()->query->get('entityName');
                } else {
                    throw new HttpException('No entityName given!', 400);
                }
                $body = [];
                if ($event->getRequest()->query->get('@self')) {
                    $body['@self'] = $event->getRequest()->query->get('@self');
                } elseif ($event->getRequest()->query->get('self')) {
                    $body['@self'] = $event->getRequest()->query->get('self');
                } elseif ($event->getRequest()->query->get('objectEntityId')) {
                    $uuid = $event->getRequest()->query->get('objectEntityId');
                }
            }

            $this->objectEntityService->setEventVariables($body, $entityName, $uuid, $componentCode);
            $notificationTopic = $componentCode . '/' . $entityName;

            if ($route == 'api_object_communications_post_collection' && $resource instanceof ObjectCommunication) {
                // Lets not create the actual ObjectCommunication objects that we can not see or do anything with.
                $this->em->remove($resource);
                $this->em->flush();
                try {
                    if ($doGet && (isset($uuid) || (isset($body['@self']) && $body['@self']))) {
                        $result = $this->objectEntityService->handleGet();
                        $responseType = Response::HTTP_OK;
                    } elseif($doGet) {
                        $result['@type'] = 'hydra:Collection';
                        $result['hydra:member'] = $this->objectEntityService->handleGetCollection(isset($body['query']) ? $body['query'] : []);
                        $result['hydra:totalItems'] = count($result['hydra:member']);
                        $responseType = Response::HTTP_OK;
                    } elseif (isset($uuid)) {
                        // Put for objectEntity with this id^
                        $result = $this->objectEntityService->handlePut();
                        $notificationAction = 'Update';
                    } elseif (isset($body['@self'])) {
                        // Check if we need to do a put if @self has already an objectEntity object in EAV
                        // Get existing objectEntity with @self
                        $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['uri' => $body['@self']]);
                        if (!empty($object)) {
                            $result = $this->objectEntityService->handlePut();
                            $notificationAction = 'Update';
                        }
                    }

                    // If not, we are doing a post
                    if (!isset($result)){
                        // post
                        $objectEntity = new ObjectEntity();
                        $this->em->persist($objectEntity);
                        $this->em->flush();
                        $result = $this->objectEntityService->handlePost($objectEntity);
                        if (isset($body['@self'])) {
                            $notificationAction = 'Update';
                        } else {
                            $notificationAction = 'Create';
                        }
                    }
                    if (!$doGet){
                        $responseType = Response::HTTP_CREATED;
                    }
                } catch (HttpException $e) {
                    if (isset($objectEntity)){
                        // Lets not create a new ObjectEntity when we get an error!
                        try {
                            $this->em->remove($objectEntity);
                            $this->em->flush();
                        } catch (\Exception $e2) {
                            throw new HttpException($e->getMessage().' While trying to throw this error eav also tried to not create an ObjectEntity object in eav, an other error was caught during this: '.$e2->getMessage(), 400);
                        }
                    }
                    throw new HttpException($e->getMessage(), 400);
                }
            } elseif ($route == 'api_object_communications_get_collection') {
                $result = $this->objectEntityService->handleGet();
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
