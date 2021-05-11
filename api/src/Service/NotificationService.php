<?php

namespace App\Service;

use App\Entity\ObjectEntity;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NotificationService
{
    private $em;
    private $commonGroundService;
    private $params;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
    }

    public function notify($topic, $action, $resource)
    {
        if($this->params->get('app_notification') == 'trueEav'
            || $this->params->get('app_notification') == 'trueEavObjectEntities') {
            $notification = [
                'topic' => $topic,
                'action' => $action,
                'resource' => $resource
            ];
            $this->commonGroundService->createResource($notification, ['component' => 'nrc', 'type' => 'notifications'], false, true, false);
        }
    }
}
