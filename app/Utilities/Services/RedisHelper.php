<?php
namespace App\Utilities\Services;

use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Support\Facades\Redis;

class RedisHelper implements RedisHelperInterface
{
    const SENT_EMAILS_HASH_PREFIX = 'sent_emails_';
    const EXPIRY_IN_SECONDS = 3600;
    /**
     * Store the id of a message along with a message subject in Redis.
     *
     * @param  mixed  $id
     * @param  string  $messageSubject
     * @param  string  $toEmailAddress
     * @return void
     */
    public function storeRecentMessage(mixed $id, string $messageSubject, string $toEmailAddress): void
    {
        $key = self::SENT_EMAILS_HASH_PREFIX . $id;

        Redis::hset($key, 'subject', $messageSubject);
        Redis::hset($key, 'to', $toEmailAddress);
        Redis::expire($key, self::EXPIRY_IN_SECONDS);
    }
}