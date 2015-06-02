<?php

namespace Moop\Bundle\HealthBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
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
        // The --if-exists flag is currently bugged.
        $this->doRun([
            'command'     => 'doctrine:database:drop',
            '--if-exists' => true,
            '--force'     => true,
        ], $output);
        
        /* Connection class appears to persist even after the entity
         * manager is reset. After the database is dropped, the DB name
         * is cleared and doctrine doesn't know where the schema should be placed.
         */
        $this->getDoctrine()->getConnection()->close();
        
        $this->doRun(['command' => 'doctrine:database:create'], $output);
        $this->doRun(['command' => 'doctrine:schema:create'],   $output);
        $this->doRun(['command' => 'moop:health:fill'],         $output);
    }
    
    /**
     * @param array           $params
     * @param OutputInterface $output
     *
     * @return int
     */
    private function doRun(array $params, OutputInterface $output)
    {
        $name    = current($params);
        $command = $this->getApplication()->find($name);
        
        $command->run(new ArrayInput(array_merge($params, [
            '-e' => $this->getContainer()->get('kernel')->getEnvironment(),
        ])), $output);
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}