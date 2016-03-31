<?php

namespace NS\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
                           'class',
                           InputArgument::REQUIRED,
                           'User class'
                   ),
                   new InputArgument(
                           'user_field',
                           InputArgument::REQUIRED,
                           'User field'
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
        $u_email = $input->getArgument('user_email');
        $newpass = $input->getArgument('newpass');
        $class   = $input->getArgument('class');
        $ufield  = $input->getArgument('user_field');
        $em      = $this->getContainer()->get('doctrine')->getEntityManager();

        try {
            $user    = $em->createQueryBuilder()->select('u')->from($class, 'u')->where('u.'.$ufield.' = :email')->setParameter('email', $u_email)->getQuery()->getSingleResult();
            $factory = $this->getContainer()->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);

            if ($user instanceof UserInterface) {
                $output->writeln("User: ".$user->getUsername());
            } elseif (method_exists($user, '__toString')) {
                $output->writeln("User: $user");
            }

            if (method_exists($user, 'resetSalt')) {
                $user->resetSalt();
            }

            $user->setPassword($encoder->encodePassword($newpass, $user->getSalt()));
            $em->persist($user);
            $em->flush();

            $output->writeln("Password updated");
        } catch (\Doctrine\ORM\NoResultException $e) {
            $output->writeln("No such user ".$e->getMessage());
        } catch (\Exception $e) {
            $output->writeln("No such user ".$e->getMessage());
        }

        $output->writeln(sprintf('Done'));
    }
}
