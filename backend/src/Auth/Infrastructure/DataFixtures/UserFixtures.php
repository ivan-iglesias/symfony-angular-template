<?php

namespace App\Auth\Infrastructure\DataFixtures;

use App\Auth\Domain\Entity\SecurityToken;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Enum\SecurityTokenType;
use App\Auth\Domain\Repository\SecurityTokenRepositoryInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SecurityTokenRepositoryInterface $tokenRepository
    ) {}

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'email' => 'admin@acme.com',
                'password' => 'admin123',
                'roles' => ['ROLE_ADMIN'],
                'name' => 'Admin',
                'lastName' => 'Acme',
                'active' => true
            ],
            [
                'email' => 'warehouse@acme.com',
                'password' => 'warehouse123',
                'roles' => ['ROLE_MANAGER'],
                'name' => 'Jefe',
                'lastName' => 'Almacén',
                'active' => true
            ],
            [
                'email' => 'driver@acme.com',
                'password' => 'driver123',
                'roles' => ['ROLE_USER'],
                'name' => 'Conductor',
                'lastName' => 'Rápido',
                'active' => true
            ],
            [
                'email' => 'disabled@acme.com',
                'password' => 'disabled123',
                'roles' => ['ROLE_USER'],
                'name' => 'Usuario',
                'lastName' => 'Inactivo',
                'active' => false
            ],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);

            $user
                ->setName($data['name'])
                ->setLastName($data['lastName'])
                ->setEmail($data['email'])
                ->setPassword($hashedPassword)
                ->setRoles($data['roles']);

            if ($data['active']) {
                $user->activate();
            } else {
                $user->deactivate();
            }

            $manager->persist($user);
            $manager->flush();

            if (!$data['active']) {
                $token = new SecurityToken(
                    $user,
                    'test_token_disabled_user',
                    SecurityTokenType::CONFIRMATION,
                    48
                );
                $this->tokenRepository->save($token);
            }
        }
    }
}
