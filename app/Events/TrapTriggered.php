<?php

namespace App\Events;

use App\Models\Trap;
use App\Models\TrapInteraction;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * حدث تفعيل الفخ — يُطلق عند تفاعل مستخدم مع فخ نفسي
 */
class TrapTriggered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Trap $trap,
        public User $user,
        public TrapInteraction $interaction,
    ) {}
}
