<?php

/**
 * @ Created on 09/03/2023 16:41
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

namespace App\Command;

use App\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Security\SecurityServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'ows:build-fixtures')]
class BuildFixturesCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command generate multiple fixtures to simulate a deployed application.');
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ParameterBagInterface $bag,
        private SecurityServiceInterface $security
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $admin = new User();
        $admin->setEmail("admin@ows.com");
        $admin->setPassword($this->passwordHasher->hashPassword($admin, "Admin_P@ss0rd"));
        $this->entityManager->persist($admin);

        $toto = new User();
        $toto->setEmail("toto@ows.com");
        $toto->setPassword($this->passwordHasher->hashPassword($toto, "Toto_P@ss0rd"));
        $this->entityManager->persist($toto);

        $tata = new User();
        $tata->setEmail("tata@ows.com");
        $tata->setPassword($this->passwordHasher->hashPassword($tata, "Tata_P@ss0rd"));
        $this->entityManager->persist($tata);

        dd(empty($this->security->getUser()));

        return Command::SUCCESS;
    }
}
