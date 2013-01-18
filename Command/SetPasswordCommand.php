<?php

namespace NS\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \NobletSolutions\NedcoBundle\Entity\User;

class SetPasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('set_password')
           ->setDescription('Set a User password')
           ->setDefinition(array(
                   new InputArgument(
                           'user_email',
                           InputArgument::REQUIRED,
                           'User email address'
                   ),
                   new InputArgument(
                           'newpass',
                           InputArgument::REQUIRED,
                           'New Password'
                   ),
           ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $u_email     = $input->getArgument('user_email');
        $newpass     = $input->getArgument('newpass');
        $em          = $this->getContainer()->get('doctrine')->getEntityManager();

        try
            {
            $user    = $em->createQueryBuilder()->select('u')->from('NobletSolutions\NedcoBundle\Entity\User','u')->where('u.login = :email')->setParameter('email',$u_email)->getQuery()->getResult();
            $user    = $user[0];
            $factory = $this->getContainer()->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);

            $output->writeln("User: ".$user->getName());
            $user->resetSalt();
            $user->setPassword($encoder->encodePassword($newpass,$user->getSalt()));
            $em->persist($user);
            $em->flush();

            $output->writeln("Password updated");
        }
        catch (\Exception $e)
        {
            $output->writeln("No such user ".$e->getMessage());
        }

        $output->writeln(sprintf('Done'));
    }
}
