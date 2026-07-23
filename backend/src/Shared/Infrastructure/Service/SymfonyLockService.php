<?php

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\LockServiceInterface;
use Symfony\Component\Lock\LockFactory;

class SymfonyLockService implements LockServiceInterface
{
    public function __construct(
        private LockFactory $lockFactory
    ) {}

    public function acquireAndExecute(string $resourceKey, callable $action, float $ttl = 10.0): bool
    {
        $lock = $this->lockFactory->createLock($resourceKey, $ttl);

        if (!$lock->acquire()) {
            return false;
        }

        try {
            $action();
            return true;
        } finally {
            $lock->release();
        }
    }
}
