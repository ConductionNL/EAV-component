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

        $participants = new Attribute();
        $participants->setName('participants');
        $participants->setType('array');
        $participants->setFormat('array');
        $participants->setDescription('An array of EAV/edu/participants urls');
        $manager->persist($participants);
        $manager->flush();

        $participations = new Attribute();
        $participations->setName('participations');
        $participations->setType('array');
        $participations->setFormat('array');
        $participations->setDescription('An array of EAV/participations urls');
        $manager->persist($participations);
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
        $learningNeedEntity->addAttribute($participants);
        $learningNeedEntity->addAttribute($participations);
        $learningNeedEntity->addAttribute($dateCreated);
        $learningNeedEntity->addAttribute($dateModified);
        $manager->persist($learningNeedEntity);
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


        // EAV participation (/verwijzing -_-)
        $status = new Attribute();
        $status->setName('status');
        $status->setType('string');
        $status->setFormat('string');
        $manager->persist($status);
        $manager->flush();

        $aanbiederId = new Attribute();
        $aanbiederId->setName('aanbiederId');
        $aanbiederId->setType('string');
        $aanbiederId->setFormat('string');
        $manager->persist($aanbiederId);
        $manager->flush();

        $aanbiederName = new Attribute();
        $aanbiederName->setName('aanbiederName');
        $aanbiederName->setType('string');
        $aanbiederName->setFormat('string');
        $manager->persist($aanbiederName);
        $manager->flush();

        $aanbiederNote = new Attribute();
        $aanbiederNote->setName('aanbiederNote');
        $aanbiederNote->setType('string');
        $aanbiederNote->setFormat('string');
        $manager->persist($aanbiederNote);
        $manager->flush();

        $offerName = new Attribute();
        $offerName->setName('offerName');
        $offerName->setType('string');
        $offerName->setFormat('string');
        $manager->persist($offerName);
        $manager->flush();

        $offerCourse = new Attribute();
        $offerCourse->setName('offerCourse');
        $offerCourse->setType('string');
        $offerCourse->setFormat('string');
        $manager->persist($offerCourse);
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

        $isFormal = new Attribute();
        $isFormal->setName('isFormal');
        $isFormal->setType('boolean');
        $isFormal->setFormat('boolean');
        $manager->persist($isFormal);
        $manager->flush();

        $groupFormation = new Attribute();
        $groupFormation->setName('groupFormation');
        $groupFormation->setType('string');
        $groupFormation->setFormat('string');
        $manager->persist($groupFormation);
        $manager->flush();

        $totalClassHours = new Attribute();
        $totalClassHours->setName('totalClassHours');
        $totalClassHours->setType('number');
        $totalClassHours->setFormat('number');
        $manager->persist($totalClassHours);
        $manager->flush();

        $certificateWillBeAwarded = new Attribute();
        $certificateWillBeAwarded->setName('certificateWillBeAwarded');
        $certificateWillBeAwarded->setType('boolean');
        $certificateWillBeAwarded->setFormat('boolean');
        $manager->persist($certificateWillBeAwarded);
        $manager->flush();

        $startDate = new Attribute();
        $startDate->setName('startDate');
        $startDate->setType('datetime');
        $startDate->setFormat('datetime');
        $manager->persist($startDate);
        $manager->flush();

        $endDate = new Attribute();
        $endDate->setName('endDate');
        $endDate->setType('datetime');
        $endDate->setFormat('datetime');
        $manager->persist($endDate);
        $manager->flush();

        $engagements = new Attribute();
        $engagements->setName('engagements');
        $engagements->setType('string');
        $engagements->setFormat('string');
        $manager->persist($engagements);
        $manager->flush();

        $presenceEngagements = new Attribute();
        $presenceEngagements->setName('presenceEngagements');
        $presenceEngagements->setType('string');
        $presenceEngagements->setFormat('string');
        $presenceEngagements->setNullable(true);
        $manager->persist($presenceEngagements);
        $manager->flush();

        $presenceStartDate = new Attribute();
        $presenceStartDate->setName('presenceStartDate');
        $presenceStartDate->setType('datetime');
        $presenceStartDate->setFormat('datetime');
        $manager->persist($presenceStartDate);
        $manager->flush();

        $presenceEndDate = new Attribute();
        $presenceEndDate->setName('presenceEndDate');
        $presenceEndDate->setType('datetime');
        $presenceEndDate->setFormat('datetime');
        $manager->persist($presenceEndDate);
        $manager->flush();

        $presenceEndParticipationReason = new Attribute();
        $presenceEndParticipationReason->setName('presenceEndParticipationReason');
        $presenceEndParticipationReason->setType('string');
        $presenceEndParticipationReason->setFormat('string');
        $manager->persist($presenceEndParticipationReason);
        $manager->flush();

        $learningNeed = new Attribute();
        $learningNeed->setName('learningNeed');
        $learningNeed->setType('string');
        $learningNeed->setFormat('string');
        $learningNeed->setDescription('A string of an eav/learning_needs url');
        $manager->persist($learningNeed);
        $manager->flush();

        $group = new Attribute();
        $group->setName('group');
        $group->setType('string');
        $group->setFormat('string');
        $group->setDescription('A string of an edu/groups url');
        $manager->persist($group);
        $manager->flush();

        $mentor = new Attribute();
        $mentor->setName('mentor');
        $mentor->setType('string');
        $mentor->setFormat('string');
        $mentor->setDescription('A string of an mrc/employees url');
        $manager->persist($mentor);
        $manager->flush();

        $results = new Attribute();
        $results->setName('results');
        $results->setType('array');
        $results->setFormat('array');
        $results->setDescription('An array of edu/results urls');
        $manager->persist($results);
        $manager->flush();

        $participationEntity = new Entity();
        $participationEntity->setType('eav/participations');
        $participationEntity->setName('participation');
        $manager->persist($participationEntity);
        $manager->flush();
        $participationEntity->addAttribute($status);
        $participationEntity->addAttribute($aanbiederId);
        $participationEntity->addAttribute($aanbiederName);
        $participationEntity->addAttribute($aanbiederNote);
        $participationEntity->addAttribute($offerName);
        $participationEntity->addAttribute($offerCourse);
        $participationEntity->addAttribute($goal);
        $participationEntity->addAttribute($topic);
        $participationEntity->addAttribute($topicOther);
        $participationEntity->addAttribute($application);
        $participationEntity->addAttribute($applicationOther);
        $participationEntity->addAttribute($level);
        $participationEntity->addAttribute($levelOther);
        $participationEntity->addAttribute($isFormal);
        $participationEntity->addAttribute($groupFormation);
        $participationEntity->addAttribute($totalClassHours);
        $participationEntity->addAttribute($certificateWillBeAwarded);
        $participationEntity->addAttribute($startDate);
        $participationEntity->addAttribute($endDate);
        $participationEntity->addAttribute($engagements);
        $participationEntity->addAttribute($presenceEngagements);
        $participationEntity->addAttribute($presenceStartDate);
        $participationEntity->addAttribute($presenceEndDate);
        $participationEntity->addAttribute($presenceEndParticipationReason);
        $participationEntity->addAttribute($learningNeed);
        $participationEntity->addAttribute($group);
        $participationEntity->addAttribute($mentor);
        $participationEntity->addAttribute($results);
        $manager->persist($participationEntity);
        $manager->flush();


        // EDU groupEntity
        $participations = new Attribute();
        $participations->setName('participations');
        $participations->setType('array');
        $participations->setFormat('array');
        $participations->setDescription('An array of eav/participations urls');
        $manager->persist($participations);
        $manager->flush();

        $groupEntity = new Entity();
        $groupEntity->setType('edu/groups');
        $groupEntity->setName('group');
        $manager->persist($groupEntity);
        $manager->flush();
        $groupEntity->addAttribute($participations);
        $manager->persist($groupEntity);
        $manager->flush();


        // MRC employee
        $participations = new Attribute();
        $participations->setName('participations');
        $participations->setType('array');
        $participations->setFormat('array');
        $participations->setDescription('An array of eav/participations urls');
        $manager->persist($participations);
        $manager->flush();

        $relevantCertificates = new Attribute();
        $relevantCertificates->setName('relevantCertificates');
        $relevantCertificates->setType('string');
        $relevantCertificates->setFormat('string');
        $relevantCertificates->setDescription('Relevant certificates that are not mentioned in skills, competences or educations');
        $relevantCertificates->setNullable(true);
        $manager->persist($relevantCertificates);
        $manager->flush();

        $referrer = new Attribute();
        $referrer->setName('referrer');
        $referrer->setType('string');
        $referrer->setFormat('string');
        $referrer->setDescription('The person who referred employee to the employer');
        $referrer->setNullable(true);
        $manager->persist($referrer);
        $manager->flush();

        $provider = new Attribute();
        $provider->setName('provider');
        $provider->setType('string');
        $provider->setFormat('string');
        $provider->setDescription('The provider for the employee');
        $provider->setNullable(true);
        $manager->persist($provider);
        $manager->flush();

        $employeeEntity = new Entity();
        $employeeEntity->setType('mrc/employees');
        $employeeEntity->setName('employee');
        $manager->persist($employeeEntity);
        $manager->flush();
        $employeeEntity->addAttribute($participations);
        $employeeEntity->addAttribute($relevantCertificates);
        $employeeEntity->addAttribute($referrer);
        $employeeEntity->addAttribute($provider);
        $manager->persist($employeeEntity);
        $manager->flush();


        // EDU resultEntity
        $participation = new Attribute();
        $participation->setName('participation');
        $participation->setType('string');
        $participation->setFormat('string');
        $participation->setDescription('A string of an eav/participations url');
        $manager->persist($participation);
        $manager->flush();

        $resultEntity = new Entity();
        $resultEntity->setType('edu/results');
        $resultEntity->setName('result');
        $manager->persist($resultEntity);
        $manager->flush();
        $resultEntity->addAttribute($participation);
        $manager->persist($resultEntity);
        $manager->flush();


        // EDU testEntity
        $testEntity = new Entity();
        $testEntity->setType('edu/tests');
        $testEntity->setName('test');
        $manager->persist($testEntity);
        $manager->flush();


        // CC personEntity/availability
        $availability = new Attribute();
        $availability->setName('availability');
        $availability->setType('array');
        $availability->setFormat('array');
        $availability->setDescription('A array that holds availability information from the person');
        $manager->persist($availability);
        $manager->flush();


        // CC personEntity
        $personEntity = new Entity();
        $personEntity->setType('cc/people');
        $personEntity->setName('person');
        $manager->persist($personEntity);
        $manager->flush();
        $personEntity->addAttribute($availability);
        $manager->persist($personEntity);
        $manager->flush();

        $courseProfessionalism = new Attribute();
        $courseProfessionalism->setName('courseProfessionalism');
        $courseProfessionalism->setType('string');
        $courseProfessionalism->setFormat('string');
        $courseProfessionalism->setDescription('The professionalism of the course');
//        $courseProfessionalism->setEnum(['PROFESSIONAL', 'VOLUNTEER', 'BOTH']);
        $courseProfessionalism->setNullable(true);
        $manager->persist($courseProfessionalism);
        $manager->flush();

        $teacherProfessionalism = new Attribute();
        $teacherProfessionalism->setName('teacherProfessionalism');
        $teacherProfessionalism->setType('string');
        $teacherProfessionalism->setFormat('string');
        $teacherProfessionalism->setDescription('The professionalism of the teacher');
//        $teacherProfessionalism->setEnum(['PROFESSIONAL', 'VOLUNTEER', 'BOTH']);
        $teacherProfessionalism->setNullable(true);
        $manager->persist($teacherProfessionalism);
        $manager->flush();

        $providesCertificate = new Attribute();
        $providesCertificate->setName('providesCertificate');
        $providesCertificate->setType('boolean');
        $providesCertificate->setFormat('boolean');
        $providesCertificate->setDescription('Denotes whether or not the education provides a certificate');
        $providesCertificate->setNullable(true);
        $manager->persist($providesCertificate);
        $manager->flush();

        // WARNING, in mrc, Entity Education plural form is set to mrc/education and not (what we expect it to be:) mrc/educations
        $educationEntity = new Entity();
        $educationEntity->setType('mrc/education');
        $educationEntity->setName('education');
        $manager->persist($educationEntity);
        $manager->flush();
        $educationEntity->addAttribute($courseProfessionalism);
        $educationEntity->addAttribute($teacherProfessionalism);
        $educationEntity->addAttribute($providesCertificate);
        $manager->persist($educationEntity);
        $manager->flush();
    }
}
