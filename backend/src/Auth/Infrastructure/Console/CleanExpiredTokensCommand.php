<?php

namespace App\Auth\Infrastructure\Console;

use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'auth:tokens:clean',
    description: 'Elimina los tokens de seguridad que han expirado',
)]
final class CleanExpiredTokensCommand extends Command
{
    public function __construct(
        private SecurityTokenRepositoryInterface $tokenRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Iniciando limpieza de tokens expirados...');

        $deletedCount = $this->tokenRepository->deleteExpiredTokens();

        if ($deletedCount > 0) {
            $io->success(sprintf('Se han eliminado %d tokens obsoletos.', $deletedCount));
        } else {
            $io->info('No se han encontrado tokens caducados para limpiar.');
        }

        return Command::SUCCESS;
    }
}
