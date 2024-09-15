<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// Execute from console with "php bin/console app:remove-unverified-users"
// or periodically from cron "0 0 * * * php bin/console app:remove-unverified-users"

#[AsCommand(
    name: 'app:remove-unverified-users',
    description: 'Remove unverified users',
)]
class RemoveUnverifiedUsersCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $unverifiedUsers = $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.isVerified = 0')
            ->getQuery()
            ->getResult()
        ;
    
        $count = 0;
        foreach ($unverifiedUsers as $user) {
            $this->entityManager->remove($user);
            $count++;
        }
    
        $this->entityManager->flush();
    
        return Command::SUCCESS;
    }
}
