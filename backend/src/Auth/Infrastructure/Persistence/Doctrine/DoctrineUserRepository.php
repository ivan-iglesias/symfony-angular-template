<?php

namespace App\Auth\Infrastructure\Persistence\Doctrine;

use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(Uuid|string $id): ?User
    {
        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }
        
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function existsByEmail(string $email): bool
    {
        return null !== $this->getEntityManager()->createQueryBuilder()
            ->select('u.id')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function deleteInactiveUsers(int $daysOld): int
    {
        $limitDate = new \DateTimeImmutable("-{$daysOld} days");

        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM App\Auth\Domain\Entity\User u WHERE u.active = false AND u.createdAt < :limitDate'
        )->setParameter('limitDate', $limitDate);

        return $query->execute();
    }
}
