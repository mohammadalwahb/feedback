<?php

namespace App\Policies;

use App\Models\FeedbackForm;
use App\Models\User;

class FeedbackFormPolicy
{
    public function update(User $user, FeedbackForm $form): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, FeedbackForm $form): bool
    {
        return $user->isAdmin();
    }
}
