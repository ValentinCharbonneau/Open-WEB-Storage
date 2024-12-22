<?php

/**
 * @ Created on 09/03/2023 15:33
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

namespace App\Command;

use App\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'ows:create-user')]
class CreateUserCommand extends Command
{
    private const BEGIN_ERROR = "<error>";
    private const END_ERROR = "</error>";

    protected function configure(): void
    {
        $this->setHelp('This command create a user.');
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ParameterBagInterface $bag
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $this->emailQuestion($user, $helper, $input, $output);

        $this->passwordQuestion($user, $helper, $input, $output);

        $adminQuestion = new Question('This user is admin ? [false] ', false);
        $admin = strtolower($helper->ask($input, $output, $adminQuestion));

        if ($admin == "true" || $admin == "yes") {
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles([]);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>User ' . $user->getEmail() . ' has been successfully created</info>');

        return Command::SUCCESS;
    }

    private function emailQuestion(User &$user, QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        $emailQuestion = new Question('Enter email of new user [toto@ows.fr] : ', 'toto@ows.fr');
        $validEmail = false;
        while (!$validEmail) {
            $user->setEmail($helper->ask($input, $output, $emailQuestion));
            $violations = $this->validator->validateProperty($user, 'email');
            if (!count($violations)) {
                $validEmail = true;
                $violations = $this->validator->validate($user);
                foreach ($violations as $violation) {
                    if ($violation->getCode() == "23bd9dbf-6b9b-41cd-a99e-4844bcf3077f") {
                        $validEmail = false;
                        $output->writeln(CreateUserCommand::BEGIN_ERROR .'Email : ' . $violation->getMessage() . CreateUserCommand::END_ERROR);
                        break;
                    }
                }
            } else {
                foreach ($violations as $violation) {
                    $output->writeln(CreateUserCommand::BEGIN_ERROR .'Email : ' . $violation->getMessage() . CreateUserCommand::END_ERROR);
                }
            }
        }
    }

    private function passwordQuestion(User &$user, QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        $pwdQuestion = new Question('Enter password of new user [P@ss0rd_] : ', 'P@ss0rd_');
        $pwdEmail = false;
        while (!$pwdEmail) {
            $pwdQuestion->setHidden(true);
            $pwd = $helper->ask($input, $output, $pwdQuestion);

            $user->setPlainPassword($pwd);
            $violations = $this->validator->validateProperty($user, 'plainPassword');
            if (!count($violations)) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
                $pwdEmail = true;
            } else {
                foreach ($violations as $violation) {
                    $output->writeln(CreateUserCommand::BEGIN_ERROR .'Password : ' . $violation->getMessage() . CreateUserCommand::END_ERROR);
                }
            }
        }
    }
}
