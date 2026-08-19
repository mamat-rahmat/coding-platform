<?php

namespace App;

enum LessonBlockType: string
{
    case TEXT = 'TEXT';
    case CODE_EXAMPLE = 'CODE_EXAMPLE';
    case HINT = 'HINT';
    case MCQ_SINGLE = 'MCQ_SINGLE';
    case MCQ_MULTIPLE = 'MCQ_MULTIPLE';
    case CODE_FILL = 'CODE_FILL';
    case CODE_REORDER = 'CODE_REORDER';
    case CODE_CHALLENGE = 'CODE_CHALLENGE';
}
