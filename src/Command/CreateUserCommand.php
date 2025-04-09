<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un utilisateur avec un mot de passe hashé.',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $questionNom = new Question('Nom de l\'utilisateur : ');
        $nom = $helper->ask($input, $output, $questionNom);

        $questionEmail = new Question('Email de l\'utilisateur : ');
        $email = $helper->ask($input, $output, $questionEmail);

        $questionPassword = new Question('Mot de passe : ');
        $questionPassword->setHidden(true);
        $questionPassword->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $questionPassword);

        $user = new Utilisateur();
        $user->setNom($nom);
        $user->setEmail($email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setMotDePasse($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>✅ Utilisateur créé avec succès !</info>');

        return Command::SUCCESS;
    }
}
