<?php

namespace Moop\Bundle\HealthBundle\Command;


use Doctrine\ORM\EntityManagerInterface;
use Moop\Bundle\FatSecretBundle\Entity\OAuthProvider;
use Moop\Bundle\HealthBundle\Entity\Goal;
use Moop\Bundle\HealthBundle\Entity\Group;
use Moop\Bundle\HealthBundle\Entity\School;
use Moop\Bundle\HealthBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('moop:health:setup')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createOAuthProvider();
        
        $school = $this->createSchools();
        $group  = $this->createDefaultGroups();
        
        $this->createDefaultGoals();
        $this->createAdminAccount($school, $group);
        
        return 0;
    }
    
    private function createSchools()
    {
        $ou_school = new School();
        
        $ou_school
            ->setName('The University of Oklahoma')
            ->setInitials('OU')
        ;
        
        $umn_school = new School();
        
        $umn_school
            ->setName('University of Minnesota')
            ->setInitials('UMN')
        ;
        
        $manager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $manager->persist($ou_school);
        $manager->persist($umn_school);
        $manager->flush();
        
        return $ou_school;
    }
    
    private function createDefaultGroups()
    {
        $manager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $manager->persist($group = new Group('Alpha'));
        $manager->persist(new Group('Beta'));
        $manager->flush();
        
        return $group;
    }
    
    private function createAdminAccount(School $school, Group $group)
    {
        $service = $this->getContainer()->get('moop.fat_secret.user.service');
        $user    = new User();
        
        $user
            ->setUsername('ashinpaugh')
            ->setDisplayName('ashinpaugh')
            ->setStudentId(113830416)
            ->setDateOfBirth('1990')
            ->setEmail('ashinpaugh@ou.edu')
            ->setFeatureSet(User::FULL_FEATURES)
            ->setFirstName('Austin')
            ->setLastName('Shinpaugh')
            ->setGender('male')
            ->setType(User::FACULTY)
            ->setSchool($school)
        ;
        
        $group->addMember($user);
        
        $service
            ->setFatOAuthTokens($user)
            ->createPasswordHash($user, 'password1')
        ;
        
        $manager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $manager->persist($user);
        $manager->flush();
    }
    
    private function createDefaultGoals()
    {
        $manager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $manager->persist(
            new Goal('Basic usage', 'search', 'Use the basic features of the app.', 10, 10, true)
        );
        
        $manager->persist(
            new Goal('Logging In', 'login', 'Login with the app.', 15, 5, true)
        );
        
        $manager->persist(
            new Goal('Tracking Calories', 'track', 'Save a few meals to your calorie diary.', 100, 25, true)
        );
        
        $manager->flush();
    }
    
    private function createOAuthProvider()
    {
        $container = $this->getContainer();
        $provider  = new OAuthProvider(
            'fat_secret',
            $container->getParameter('moop.fat_secret.consumer_key'),
            $container->getParameter('moop.fat_secret.consumer_secret'),
            'http://platform.fatsecret.com/rest/server.api',
            OAuthProvider::v1
        );
        
        $manager = $container->get('doctrine.orm.default_entity_manager');
        $manager->persist($provider);
        $manager->flush();
    }
}