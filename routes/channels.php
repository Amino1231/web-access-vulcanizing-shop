<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Private user channel — used by Laravel's built-in notification
 * broadcasting to deliver notifications to the correct logged-in user.
 *
 * Each user (owner, admin, customer, or mechanic) can only listen to
 * their OWN channel. Attempting to listen to another user's channel
 * returns 403.
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});