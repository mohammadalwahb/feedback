<?php

namespace App\Enums;

enum FeedbackQuestionType: string
{
    case Likert5 = 'likert_5';
    case YesNo = 'yes_no';
    case MultipleChoice = 'multiple_choice';
    case Text = 'text';
    case Note = 'note';
}
