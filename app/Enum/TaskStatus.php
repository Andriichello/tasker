<?php

namespace App\Enum;

enum TaskStatus: string
{
    case ToDo = 'to-do';
    case InProgress = 'in-progress';
    case Done = 'done';
    case Canceled = 'canceled';
}
