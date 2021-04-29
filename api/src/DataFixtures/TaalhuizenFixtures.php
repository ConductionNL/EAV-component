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
        $topic->setEnum(['DUTCH_READING', 'DUTCH_WRITING', 'MATH_NUMBERS', 'MATH_PROPORTION', 'MATH_GEOMETRY', 'MATH_LINKS', 'DIGITAL_USING_ICT_SYSTEMS', 'DIGITAL_SEARCHING_INFORMATION', 'DIGITAL_PROCESSING_INFORMATION', 'DIGITAL_COMMUNICATION', 'KNOWLEDGE', 'SKILLS', 'ATTITUDE', 'BEHAVIOUR', 'OTHER']);
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
        $application->setEnum(['FAMILY_AND_PARENTING', 'LABOR_MARKET_AND_WORK', 'HEALTH_AND_WELLBEING', 'ADMINISTRATION_AND_FINANCE', 'HOUSING_AND_NEIGHBORHOOD', 'SELFRELIANCE', 'OTHER']);
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
        $level->setEnum(['INFLOW', 'NLQF1', 'NLQF2', 'NLQF3', 'NLQF4', 'OTHER']);
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
        $offerDifference->setEnum(['NO', 'YES_DISTANCE', 'YES_WAITINGLIST', 'YES_OTHER']);
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
        $participants->setName('participants'); //EDU!
        $participants->setType('array');
        $participants->setFormat('array');
        $participants->setDescription('An array of edu/participants urls');
        $manager->persist($participants);
        $manager->flush();

        $participations = new Attribute();
        $participations->setName('participations'); //EAV!
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

        /**
         * EAV Documents
         */
        $documentData = new Attribute();
        $documentData->setName('base64data');
        $documentData->setType('string');
        $documentData->setFormat('string');
        $manager->persist($documentData);
        $manager->flush();

        $documentFilename = new Attribute();
        $documentFilename->setName('filename');
        $documentFilename->setType('string');
        $documentFilename->setFormat('string');
        $manager->persist($documentFilename);
        $manager->flush();

        $documentProviderEmployee = new Attribute();
        $documentProviderEmployee->setName('aanbiederEmployeeId');
        $documentProviderEmployee->setType('string');
        $documentProviderEmployee->setFormat('string');
        $manager->persist($documentProviderEmployee);
        $manager->flush();

        $documentStudent = new Attribute();
        $documentStudent->setName('studentId');
        $documentStudent->setType('string');
        $documentStudent->setFormat('string');
        $manager->persist($documentStudent);
        $manager->flush();

        $documentEntity = new Entity();
        $documentEntity->setType('eav/documents');
        $documentEntity->setName('document');
        $manager->persist($documentEntity);
        $manager->flush();
        $documentEntity->addAttribute($documentData);
        $documentEntity->addAttribute($documentFilename);
        $documentEntity->addAttribute($documentFilename);
        $documentEntity->addAttribute($documentStudent);


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
        $status->setEnum(['REFERRED', 'ACTIVE', 'COMPLETED']);
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
        $topic->setEnum(['DUTCH_READING', 'DUTCH_WRITING', 'MATH_NUMBERS', 'MATH_PROPORTION', 'MATH_GEOMETRY', 'MATH_LINKS', 'DIGITAL_USING_ICT_SYSTEMS', 'DIGITAL_SEARCHING_INFORMATION', 'DIGITAL_PROCESSING_INFORMATION', 'DIGITAL_COMMUNICATION', 'KNOWLEDGE', 'SKILLS', 'ATTITUDE', 'BEHAVIOUR', 'OTHER']);
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
        $application->setEnum(['FAMILY_AND_PARENTING', 'LABOR_MARKET_AND_WORK', 'HEALTH_AND_WELLBEING', 'ADMINISTRATION_AND_FINANCE', 'HOUSING_AND_NEIGHBORHOOD', 'SELFRELIANCE', 'OTHER']);
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
        $level->setEnum(['INFLOW', 'NLQF1', 'NLQF2', 'NLQF3', 'NLQF4', 'OTHER']);
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
        $groupFormation->setEnum(['INDIVIDUALLY', 'IN_A_GROUP']);
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
        $presenceStartDate->setNullable(true);
        $manager->persist($presenceStartDate);
        $manager->flush();

        $presenceEndDate = new Attribute();
        $presenceEndDate->setName('presenceEndDate');
        $presenceEndDate->setType('datetime');
        $presenceEndDate->setFormat('datetime');
        $presenceEndDate->setNullable(true);
        $manager->persist($presenceEndDate);
        $manager->flush();

        $presenceEndParticipationReason = new Attribute();
        $presenceEndParticipationReason->setName('presenceEndParticipationReason');
        $presenceEndParticipationReason->setType('string');
        $presenceEndParticipationReason->setFormat('string');
        $presenceEndParticipationReason->setEnum(['MOVED', 'JOB', 'ILLNESS', 'DEATH', 'COMPLETED_SUCCESSFULLY', 'FAMILY_CIRCUMSTANCES', 'DOES_NOT_MEET_EXPECTATIONS', 'OTHER']);
        $presenceEndParticipationReason->setNullable(true);
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
        $group->setNullable(true);
        $manager->persist($group);
        $manager->flush();

        $mentor = new Attribute();
        $mentor->setName('mentor');
        $mentor->setType('string');
        $mentor->setFormat('string');
        $mentor->setDescription('A string of an mrc/employees url');
        $mentor->setNullable(true);
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

        $outComesGoal = new Attribute();
        $outComesGoal->setName('goal');
        $outComesGoal->setType('string');
        $outComesGoal->setFormat('string');
        $outComesGoal->setDescription('A string of the goal of this group');
        $manager->persist($outComesGoal);
        $manager->flush();

        $outComesTopic = new Attribute();
        $outComesTopic->setName('topic');
        $outComesTopic->setType('string');
        $outComesTopic->setFormat('string');
        $outComesTopic->setDescription('the topic of this group');
        $outComesTopic->setEnum(['DUTCH_READING','DUTCH_WRITING','MATH_NUMBERS','MATH_PROPORTION','MATH_GEOMETRY','MATH_LINKS','DIGITAL_USING_ICT_SYSTEMS','DIGITAL_SEARCHING_INFORMATION', 'DIGITAL_PROCESSING_INFORMATION','DIGITAL_COMMUNICATION','KNOWLEDGE','SKILLS','ATTITUDE','BEHAVIOUR','OTHER']);
        $manager->persist($outComesTopic);
        $manager->flush();

        $outComesTopicOther = new Attribute();
        $outComesTopicOther->setName('topicOther');
        $outComesTopicOther->setType('string');
        $outComesTopicOther->setFormat('string');
        $outComesTopicOther->setDescription('the topic of this group if topic is not in list');
        $outComesTopicOther->setNullable(true);
        $manager->persist($outComesTopicOther);
        $manager->flush();

        $outComesApplication = new Attribute();
        $outComesApplication->setName('application');
        $outComesApplication->setType('string');
        $outComesApplication->setFormat('string');
        $outComesApplication->setDescription('application of what is being learned');
        $outComesApplication->setEnum(['FAMILY_AND_PARENTING','LABOR_MARKET_AND_WORK','HEALTH_AND_WELLBEING','ADMINISTRATION_AND_FINANCE','HOUSING_AND_NEIGHBORHOOD','SELFRELIANCE','OTHER']);
        $manager->persist($outComesApplication);
        $manager->flush();

        $outComesApplicationOther = new Attribute();
        $outComesApplicationOther->setName('applicationOther');
        $outComesApplicationOther->setType('string');
        $outComesApplicationOther->setFormat('string');
        $outComesApplicationOther->setDescription('application of what is being learned if application not in list');
        $outComesApplicationOther->setNullable(true);
        $manager->persist($applicationOther);
        $manager->flush();

        $outComesLevel = new Attribute();
        $outComesLevel->setName('level');
        $outComesLevel->setType('string');
        $outComesLevel->setFormat('string');
        $outComesLevel->setDescription('the level that will be taught');
        $outComesLevel->setEnum(['INFLOW','NLQF1','NLQF2','NLQF3','NLQF4','OTHER']);
        $manager->persist($outComesLevel);
        $manager->flush();

        $outComesLevelOther = new Attribute();
        $outComesLevelOther->setName('levelOther');
        $outComesLevelOther->setType('string');
        $outComesLevelOther->setFormat('string');
        $outComesLevelOther->setDescription('the level that will be taught if level is not in list');
        $outComesLevelOther->setNullable(true);
        $manager->persist($outComesLevelOther);
        $manager->flush();

        $detailsIsFormal = new Attribute();
        $detailsIsFormal->setName('isFormal');
        $detailsIsFormal->setType('boolean');
        $detailsIsFormal->setFormat('boolean');
        $detailsIsFormal->setDescription('Denotes whether or not this is formal');
        $manager->persist($isFormal);
        $manager->flush();

        $detailsCertificateWillBeAwarded = new Attribute();
        $detailsCertificateWillBeAwarded->setName('certificateWillBeAwarded');
        $detailsCertificateWillBeAwarded->setType('boolean');
        $detailsCertificateWillBeAwarded->setFormat('boolean');
        $detailsCertificateWillBeAwarded->setDescription('Denotes if completion of this course grants a certificate');
        $manager->persist($certificateWillBeAwarded);
        $manager->flush();

        $generalLocation = new Attribute();
        $generalLocation->setName('location');
        $generalLocation->setType('string');
        $generalLocation->setFormat('string');
        $generalLocation->setDescription('The location the class will take place');
        $manager->persist($generalLocation);
        $manager->flush();

        $generalEvaluation = new Attribute();
        $generalEvaluation->setName('evaluation');
        $generalEvaluation->setType('string');
        $generalEvaluation->setFormat('string');
        $generalEvaluation->setDescription('The evaluation of this group');
        $manager->persist($generalEvaluation);
        $manager->flush();

        $groupEntity = new Entity();
        $groupEntity->setType('edu/groups');
        $groupEntity->setName('group');
        $manager->persist($groupEntity);
        $manager->flush();
        $groupEntity->addAttribute($participations);
        $groupEntity->addAttribute($outComesGoal);
        $groupEntity->addAttribute($outComesTopic);
        $groupEntity->addAttribute($outComesTopicOther);
        $groupEntity->addAttribute($outComesApplication);
        $groupEntity->addAttribute($outComesApplicationOther);
        $groupEntity->addAttribute($outComesLevel);
        $groupEntity->addAttribute($outComesLevelOther);
        $groupEntity->addAttribute($detailsIsFormal);
        $groupEntity->addAttribute($detailsCertificateWillBeAwarded);
        $groupEntity->addAttribute($generalLocation);
        $groupEntity->addAttribute($generalEvaluation);
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
        $availability->setNullable(true);
        $manager->persist($availability);
        $manager->flush();

        // CC personEntity/foundVia
        $foundVia = new Attribute();
        $foundVia->setName('foundVia');
        $foundVia->setType('string');
        $foundVia->setFormat('string');
        $foundVia->setDescription('A string that holds the found via information from the person');
        $manager->persist($foundVia);
        $manager->flush();

        // CC personEntity/wentToTaalhuisBefore
        $wentToTaalhuisBefore = new Attribute();
        $wentToTaalhuisBefore->setName('wentToTaalhuisBefore');
        $wentToTaalhuisBefore->setType('boolean');
        $wentToTaalhuisBefore->setFormat('boolean');
        $wentToTaalhuisBefore->setDescription('A bool that holds if the person went to taalhuis before');
        $manager->persist($wentToTaalhuisBefore);
        $manager->flush();

        // CC personEntity/wentToTaalhuisBeforeReason
        $wentToTaalhuisBeforeReason = new Attribute();
        $wentToTaalhuisBeforeReason->setName('wentToTaalhuisBeforeReason');
        $wentToTaalhuisBeforeReason->setType('boolean');
        $wentToTaalhuisBeforeReason->setFormat('boolean');
        $wentToTaalhuisBeforeReason->setDescription('A boolean that holds the reason this person went to taalhuis before');
        $manager->persist($wentToTaalhuisBeforeReason);
        $manager->flush();

        // CC personEntity/wentToTaalhuisBeforeYear
        $wentToTaalhuisBeforeYear = new Attribute();
        $wentToTaalhuisBeforeYear->setName('wentToTaalhuisBeforeYear');
        $wentToTaalhuisBeforeYear->setType('string');
        $wentToTaalhuisBeforeYear->setFormat('string');
        $wentToTaalhuisBeforeYear->setDescription('A string that holds the date this person went to taalhuis before');
        $manager->persist($wentToTaalhuisBeforeYear);
        $manager->flush();

        // CC personEntity/network
        $network = new Attribute();
        $network->setName('network');
        $network->setType('string');
        $network->setFormat('string');
        $network->setDescription('A string that holds the network of this person');
        $manager->persist($network);
        $manager->flush();

        // CC personEntity/participationLadder
        $participationLadder = new Attribute();
        $participationLadder->setName('participationLadder');
        $participationLadder->setType('integer');
        $participationLadder->setFormat('integer');
        $participationLadder->setDescription('A int that holds the participationLadder of this person');
        $manager->persist($participationLadder);
        $manager->flush();

        // CC personEntity/dutchNTDetails
        $dutchNTDetails = new Attribute();
        $dutchNTDetails->setName('dutchNTDetails');
        $dutchNTDetails->setType('string');
        $dutchNTDetails->setFormat('string');
        $dutchNTDetails->setDescription('A string that holds the Dutch NT details of this person');
        $manager->persist($dutchNTDetails);
        $manager->flush();

        // CC personEntity/inNetherlandsSinceYear
        $inNetherlandsSinceYear = new Attribute();
        $inNetherlandsSinceYear->setName('inNetherlandsSinceYear');
        $inNetherlandsSinceYear->setType('string');
        $inNetherlandsSinceYear->setFormat('string');
        $inNetherlandsSinceYear->setDescription('A date that holds the inNetherlandsSinceYear of this person');
        $manager->persist($inNetherlandsSinceYear);
        $manager->flush();

        // CC personEntity/languageInDailyLife
        $languageInDailyLife = new Attribute();
        $languageInDailyLife->setName('languageInDailyLife');
        $languageInDailyLife->setType('string');
        $languageInDailyLife->setFormat('string');
        $languageInDailyLife->setDescription('A string that holds the languageInDailyLife of this person');
        $manager->persist($languageInDailyLife);
        $manager->flush();

        // CC personEntity/knowsLatinAlphabet
        $knowsLatinAlphabet = new Attribute();
        $knowsLatinAlphabet->setName('knowsLatinAlphabet');
        $knowsLatinAlphabet->setType('boolean');
        $knowsLatinAlphabet->setFormat('boolean');
        $knowsLatinAlphabet->setDescription('A bool that tells if this person knows the latin alphabet');
        $manager->persist($knowsLatinAlphabet);
        $manager->flush();

        // CC personEntity/lastKnownLevel
        $lastKnownLevel = new Attribute();
        $lastKnownLevel->setName('lastKnownLevel');
        $lastKnownLevel->setType('string');
        $lastKnownLevel->setFormat('string');
        $lastKnownLevel->setDescription('A string that tells if this person last known level');
        $manager->persist($lastKnownLevel);
        $manager->flush();

        // CC personEntity/didSignPermissionForm
        $didSignPermissionForm = new Attribute();
        $didSignPermissionForm->setName('didSignPermissionForm');
        $didSignPermissionForm->setType('boolean');
        $didSignPermissionForm->setFormat('boolean');
        $didSignPermissionForm->setDescription('A bool that tells if this person didSignPermissionForm');
        $manager->persist($didSignPermissionForm);
        $manager->flush();

        // CC personEntity/hasPermissionToShareDataWithAanbieders
        $hasPermissionToShareDataWithAanbieders = new Attribute();
        $hasPermissionToShareDataWithAanbieders->setName('hasPermissionToShareDataWithAanbieders');
        $hasPermissionToShareDataWithAanbieders->setType('boolean');
        $hasPermissionToShareDataWithAanbieders->setFormat('boolean');
        $hasPermissionToShareDataWithAanbieders->setDescription('A bool that tells if this person hasPermissionToShareDataWithAanbieders');
        $manager->persist($hasPermissionToShareDataWithAanbieders);
        $manager->flush();

        // CC personEntity/hasPermissionToShareDataWithLibraries
        $hasPermissionToShareDataWithLibraries = new Attribute();
        $hasPermissionToShareDataWithLibraries->setName('hasPermissionToShareDataWithLibraries');
        $hasPermissionToShareDataWithLibraries->setType('boolean');
        $hasPermissionToShareDataWithLibraries->setFormat('boolean');
        $hasPermissionToShareDataWithLibraries->setDescription('A bool that tells if this person hasPermissionToShareDataWithLibraries');
        $manager->persist($hasPermissionToShareDataWithLibraries);
        $manager->flush();


        // CC personEntity/hasPermissionToSendInformationAboutLibraries
        $hasPermissionToSendInformationAboutLibraries = new Attribute();
        $hasPermissionToSendInformationAboutLibraries->setName('hasPermissionToSendInformationAboutLibraries');
        $hasPermissionToSendInformationAboutLibraries->setType('boolean');
        $hasPermissionToSendInformationAboutLibraries->setFormat('boolean');
        $hasPermissionToSendInformationAboutLibraries->setDescription('A bool that tells if this person hasPermissionToSendInformationAboutLibraries');
        $manager->persist($hasPermissionToSendInformationAboutLibraries);
        $manager->flush();

        // CC personEntity
        $personEntity = new Entity();
        $personEntity->setType('cc/people');
        $personEntity->setName('person');
        $manager->persist($personEntity);
        $manager->flush();
        $personEntity->addAttribute($availability);
        $personEntity->addAttribute($foundVia);
        $personEntity->addAttribute($wentToTaalhuisBefore);
        $personEntity->addAttribute($wentToTaalhuisBeforeReason);
        $personEntity->addAttribute($wentToTaalhuisBeforeYear);
        $personEntity->addAttribute($participationLadder);
        $personEntity->addAttribute($dutchNTDetails);
        $personEntity->addAttribute($inNetherlandsSinceYear);
        $personEntity->addAttribute($languageInDailyLife);
        $personEntity->addAttribute($knowsLatinAlphabet);
        $personEntity->addAttribute($lastKnownLevel);
        $personEntity->addAttribute($didSignPermissionForm);
        $personEntity->addAttribute($hasPermissionToShareDataWithAanbieders);
        $personEntity->addAttribute($hasPermissionToShareDataWithLibraries);
        $personEntity->addAttribute($hasPermissionToSendInformationAboutLibraries);
        $manager->persist($personEntity);
        $manager->flush();

        $courseProfessionalism = new Attribute();
        $courseProfessionalism->setName('courseProfessionalism');
        $courseProfessionalism->setType('string');
        $courseProfessionalism->setFormat('string');
        $courseProfessionalism->setDescription('The professionalism of the course');
        $courseProfessionalism->setEnum(['PROFESSIONAL', 'VOLUNTEER', 'BOTH']);
        $courseProfessionalism->setNullable(true);
        $manager->persist($courseProfessionalism);
        $manager->flush();

        $teacherProfessionalism = new Attribute();
        $teacherProfessionalism->setName('teacherProfessionalism');
        $teacherProfessionalism->setType('string');
        $teacherProfessionalism->setFormat('string');
        $teacherProfessionalism->setDescription('The professionalism of the teacher');
        $teacherProfessionalism->setEnum(['PROFESSIONAL', 'VOLUNTEER', 'BOTH']);
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
