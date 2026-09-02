<?php

namespace App\Auth\Infrastructure\Console;

use App\Auth\Domain\Repository\UserRepositoryInterface;
use App\Auth\Domain\Service\RefreshTokenGeneratorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'auth:users:revoke-sessions',
    description: 'Revoca todos los Refresh Tokens y cierra la sesión de un usuario en todos los dispositivos.'
)]
final class RevokeUserSessionsCommand extends Command
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'email',
            InputArgument::REQUIRED,
            'El correo electrónico del usuario cuyas sesiones se van a revocar.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        // 1. Buscamos el usuario en el repositorio de Dominio
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            $io->error(sprintf('No se encontró ningún usuario registrado con el email "%s".', $email));
            return Command::FAILURE;
        }

        // 2. Invocamos la revocación masiva en Redis
        $this->refreshTokenGenerator->revokeAllForUser($user);

        $io->success(sprintf('Se han revocado con éxito todas las sesiones activas para "%s".', $email));

        return Command::SUCCESS;
    }
}
