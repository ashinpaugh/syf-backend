<?php

namespace Moop\Bundle\HealthBundle\Command;


use Moop\Bundle\HealthBundle\Entity\School;
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
            ->setName('moop:backend:setup')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createSchools();
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
    }
}