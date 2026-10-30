<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case Planned = 'direncanakan';
    case Finished = 'selesai';
}
