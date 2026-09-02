<?php

namespace App\Auth\Infrastructure\Console;

use App\Auth\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'auth:users:purge',
    description: 'Elimina usuarios que no activaron su cuenta tras X días',
)]
final class PurgeInactiveUsersCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            'd',
            InputOption::VALUE_REQUIRED,
            '¿Cuántos días de antigüedad debe tener el usuario inactivo para ser borrado?',
            30
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = (int) $input->getOption('days');

        $io->note(sprintf('Buscando usuarios inactivos con más de %d días...', $days));

        $deletedCount = $this->userRepository->deleteInactiveUsers($days);

        if ($deletedCount > 0) {
            $io->success(sprintf('Se han purgado %d usuarios inactivos.', $deletedCount));
        } else {
            $io->info('No se han encontrado usuarios para purgar.');
        }

        return Command::SUCCESS;
    }
}
