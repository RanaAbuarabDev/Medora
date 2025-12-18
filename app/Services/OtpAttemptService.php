<?php 

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OtpAttemptService
{
    protected int $maxAttempts = 5;
    protected int $lockMinutes = 30;

    protected function attemptsKey(string $email): string
    {
        return "otp_attempts:{$email}";
    }

    protected function lockKey(string $email): string
    {
        return "otp_lock:{$email}";
    }

    public function increment(string $email): int
    {
        return Cache::increment(
            $this->attemptsKey($email)
        );
    }

    public function exceeded(string $email): bool
    {
        return Cache::get($this->attemptsKey($email), 0) >= $this->maxAttempts;
    }

    public function lock(string $email): void
    {
        Cache::put(
            $this->lockKey($email),
            now()->addMinutes($this->lockMinutes)->timestamp,
            now()->addMinutes($this->lockMinutes)
        );

        Cache::forget($this->attemptsKey($email));
    }

    public function isLocked(string $email): bool
    {
        return Cache::has($this->lockKey($email));
    }

    public function remainingTime(string $email): int
    {
        $until = Cache::get($this->lockKey($email));
        return max(0, $until - now()->timestamp);
    }

    public function clear(string $email): void
    {
        Cache::forget($this->attemptsKey($email));
        Cache::forget($this->lockKey($email));
    }
}
