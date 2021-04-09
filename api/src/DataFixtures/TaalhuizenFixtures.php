<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Entity;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TaalhuizenFixtures extends Fixture
{
    private $params;
    /**
     * @var CommonGroundService
     */
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public function load(ObjectManager $manager)
    {
        if (
            !$this->params->get('app_build_all_fixtures') &&
            $this->params->get('app_domain') != 'taalhuizen-bisc.commonground.nu' && strpos($this->params->get('app_domain'), 'taalhuizen-bisc.commonground.nu') == false
        ) {
            return false;
        }

        // EAV learningNeedEntity
        $description = new Attribute();
        $description->setName('description');
        $description->setType('string');
        $description->setFormat('string');
        $manager->persist($description);
        $manager->flush();

        $motivation = new Attribute();
        $motivation->setName('motivation');
        $motivation->setType('string');
        $motivation->setFormat('string');
        $manager->persist($motivation);
        $manager->flush();

        $goal = new Attribute();
        $goal->setName('goal');
        $goal->setType('string');
        $goal->setFormat('string');
        $manager->persist($goal);
        $manager->flush();

        $topic = new Attribute();
        $topic->setName('topic');
        $topic->setType('string');
        $topic->setFormat('string');
        $manager->persist($topic);
        $manager->flush();

        $topicOther = new Attribute();
        $topicOther->setName('topicOther');
        $topicOther->setType('string');
        $topicOther->setFormat('string');
        $topicOther->setNullable(true);
        $manager->persist($topicOther);
        $manager->flush();

        $application = new Attribute();
        $application->setName('application');
        $application->setType('string');
        $application->setFormat('string');
        $manager->persist($application);
        $manager->flush();

        $applicationOther = new Attribute();
        $applicationOther->setName('applicationOther');
        $applicationOther->setType('string');
        $applicationOther->setFormat('string');
        $applicationOther->setNullable(true);
        $manager->persist($applicationOther);
        $manager->flush();

        $level = new Attribute();
        $level->setName('level');
        $level->setType('string');
        $level->setFormat('string');
        $manager->persist($level);
        $manager->flush();

        $levelOther = new Attribute();
        $levelOther->setName('levelOther');
        $levelOther->setType('string');
        $levelOther->setFormat('string');
        $levelOther->setNullable(true);
        $manager->persist($levelOther);
        $manager->flush();

        $desiredOffer = new Attribute();
        $desiredOffer->setName('desiredOffer');
        $desiredOffer->setType('string');
        $desiredOffer->setFormat('string');
        $manager->persist($desiredOffer);
        $manager->flush();

        $advisedOffer = new Attribute();
        $advisedOffer->setName('advisedOffer');
        $advisedOffer->setType('string');
        $advisedOffer->setFormat('string');
        $manager->persist($advisedOffer);
        $manager->flush();

        $offerDifference = new Attribute();
        $offerDifference->setName('offerDifference');
        $offerDifference->setType('string');
        $offerDifference->setFormat('string');
        $manager->persist($offerDifference);
        $manager->flush();

        $offerDifferenceOther = new Attribute();
        $offerDifferenceOther->setName('offerDifferenceOther');
        $offerDifferenceOther->setType('string');
        $offerDifferenceOther->setFormat('string');
        $offerDifferenceOther->setNullable(true);
        $manager->persist($offerDifferenceOther);
        $manager->flush();

        $offerEngagements = new Attribute();
        $offerEngagements->setName('offerEngagements');
        $offerEngagements->setType('string');
        $offerEngagements->setFormat('string');
        $offerEngagements->setNullable(true);
        $manager->persist($offerEngagements);
        $manager->flush();

        $groups = new Attribute();
        $groups->setName('groups');
        $groups->setType('array');
        $groups->setFormat('array');
        $groups->setDescription('An array of EAV/edu/groups urls');
        $manager->persist($groups);
        $manager->flush();

        $participants = new Attribute();
        $participants->setName('participants');
        $participants->setType('array');
        $participants->setFormat('array');
        $participants->setDescription('An array of EAV/edu/participants urls');
        $manager->persist($participants);
        $manager->flush();

        $tests = new Attribute();
        $tests->setName('tests');
        $tests->setType('array');
        $tests->setFormat('array');
        $tests->setDescription('An array of EAV/edu/tests urls');
        $manager->persist($tests);
        $manager->flush();

        $results = new Attribute();
        $results->setName('results');
        $results->setType('array');
        $results->setFormat('array');
        $results->setDescription('An array of EAV/edu/results urls');
        $manager->persist($results);
        $manager->flush();

        $dateCreated = new Attribute();
        $dateCreated->setName('dateCreated');
        $dateCreated->setType('datetime');
        $dateCreated->setFormat('datetime');
        $manager->persist($dateCreated);
        $manager->flush();

        $dateModified = new Attribute();
        $dateModified->setName('dateModified');
        $dateModified->setType('datetime');
        $dateModified->setFormat('datetime');
        $manager->persist($dateModified);
        $manager->flush();

        $learningNeedEntity = new Entity();
        $learningNeedEntity->setType('eav/learning_needs');
        $learningNeedEntity->setName('learningNeed');
        $manager->persist($learningNeedEntity);
        $manager->flush();
        $learningNeedEntity->addAttribute($description);
        $learningNeedEntity->addAttribute($motivation);
        $learningNeedEntity->addAttribute($goal);
        $learningNeedEntity->addAttribute($topic);
        $learningNeedEntity->addAttribute($topicOther);
        $learningNeedEntity->addAttribute($application);
        $learningNeedEntity->addAttribute($applicationOther);
        $learningNeedEntity->addAttribute($level);
        $learningNeedEntity->addAttribute($levelOther);
        $learningNeedEntity->addAttribute($desiredOffer);
        $learningNeedEntity->addAttribute($advisedOffer);
        $learningNeedEntity->addAttribute($offerDifference);
        $learningNeedEntity->addAttribute($offerDifferenceOther);
        $learningNeedEntity->addAttribute($offerEngagements);
        $learningNeedEntity->addAttribute($groups);
        $learningNeedEntity->addAttribute($participants);
        $learningNeedEntity->addAttribute($tests);
        $learningNeedEntity->addAttribute($results);
        $learningNeedEntity->addAttribute($dateCreated);
        $learningNeedEntity->addAttribute($dateModified);
        $manager->persist($learningNeedEntity);
        $manager->flush();


        // EDU groupEntity
        $learningNeeds = new Attribute();
        $learningNeeds->setName('learningNeeds');
        $learningNeeds->setType('array');
        $learningNeeds->setFormat('array');
        $learningNeeds->setDescription('An array of eav/learning_needs urls');
        $manager->persist($learningNeeds);
        $manager->flush();

        $groupEntity = new Entity();
        $groupEntity->setType('edu/groups');
        $groupEntity->setName('group');
        $manager->persist($groupEntity);
        $manager->flush();
        $groupEntity->addAttribute($learningNeeds);
        $manager->persist($groupEntity);
        $manager->flush();


        // EDU participantEntity
        $learningNeeds = new Attribute();
        $learningNeeds->setName('learningNeeds');
        $learningNeeds->setType('array');
        $learningNeeds->setFormat('array');
        $learningNeeds->setDescription('An array of eav/learning_needs urls');
        $manager->persist($learningNeeds);
        $manager->flush();

        $participantEntity = new Entity();
        $participantEntity->setType('edu/participants');
        $participantEntity->setName('participant');
        $manager->persist($participantEntity);
        $manager->flush();
        $participantEntity->addAttribute($learningNeeds);
        $manager->persist($participantEntity);
        $manager->flush();


        // EDU testEntity
        $learningNeeds = new Attribute();
        $learningNeeds->setName('learningNeeds');
        $learningNeeds->setType('array');
        $learningNeeds->setFormat('array');
        $learningNeeds->setDescription('An array of eav/learning_needs urls');
        $manager->persist($learningNeeds);
        $manager->flush();

        $testEntity = new Entity();
        $testEntity->setType('edu/tests');
        $testEntity->setName('test');
        $manager->persist($testEntity);
        $manager->flush();
        $testEntity->addAttribute($learningNeeds);
        $manager->persist($testEntity);
        $manager->flush();


        // EDU resultEntity
        $learningNeed = new Attribute();
        $learningNeed->setName('learningNeed');
        $learningNeed->setType('string');
        $learningNeed->setFormat('string');
        $learningNeed->setDescription('A string of an eav/learning_needs url');
        $manager->persist($learningNeed);
        $manager->flush();

        $resultEntity = new Entity();
        $resultEntity->setType('edu/results');
        $resultEntity->setName('result');
        $manager->persist($resultEntity);
        $manager->flush();
        $resultEntity->addAttribute($learningNeed);
        $manager->persist($resultEntity);
        $manager->flush();


        // CC personEntity
        $personEntity = new Entity();
        $personEntity->setType('cc/people');
        $personEntity->setName('person');
        $manager->persist($personEntity);
        $manager->flush();
    }
}
