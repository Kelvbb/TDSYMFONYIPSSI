<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur (email, mot de passe).',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'Prénom', 'Admin')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Nom', 'Admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $existing->setRoles([User::ROLE_ADMIN]);
            $existing->setIsActive(true);
            $existing->setPassword($this->passwordHasher->hashPassword($existing, $password));
            $existing->setFirstName($firstName);
            $existing->setLastName($lastName);
            $this->em->flush();
            $io->success("Compte existant mis à jour en administrateur : {$email}");
            return Command::SUCCESS;
        }

        $admin = new User();
        $admin->setEmail($email);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $password));
        $admin->setFirstName($firstName);
        $admin->setLastName($lastName);
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setIsActive(true);
        $this->em->persist($admin);
        $this->em->flush();

        $io->success("Administrateur créé : {$email}");
        return Command::SUCCESS;
    }
}
