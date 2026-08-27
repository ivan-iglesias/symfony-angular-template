<?php

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\User;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function findById(Uuid|string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function save(User $user): void;

    public function existsByEmail(string $email): bool;

    public function deleteInactiveUsers(int $daysOld): int;
}
