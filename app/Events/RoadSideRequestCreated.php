<?php

namespace App\Events;

use App\Models\RoadSideRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoadSideRequestCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public RoadSideRequest $request) {}

    public function broadcastOn(): Channel
    {
        return new Channel('roadside');
    }

    public function broadcastAs(): string
    {
        return 'roadside.request.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->request->id,
            'request_number' => $this->request->request_number,
            'problem_type'   => $this->request->problem_type,
            'lat'            => (float) $this->request->latitude,
            'lng'            => (float) $this->request->longitude,
            'status'         => $this->request->status,
        ];
    }
}