<?php

namespace App;

enum LessonBlockType: string
{
    case TEXT = 'TEXT';
    case CODE_EXAMPLE = 'CODE_EXAMPLE';
    case MCQ_SINGLE = 'MCQ_SINGLE';
    case CODE_EXERCISE = 'CODE_EXERCISE';
    case HINT = 'HINT';
}
