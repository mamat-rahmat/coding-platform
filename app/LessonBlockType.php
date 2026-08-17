<?php

namespace App;

enum LessonBlockType: string
{
    case TEXT = 'TEXT';
    case CODE_EXAMPLE = 'CODE_EXAMPLE';
    case QUIZ = 'QUIZ';
    case CODE_EXERCISE = 'CODE_EXERCISE';
    case HINT = 'HINT';
}
