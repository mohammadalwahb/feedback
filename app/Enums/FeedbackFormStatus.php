<?php

namespace App\Enums;

enum FeedbackFormStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
}
