<?php

namespace App\DataFixtures;

enum MessageStatusEnum: string
{
    case SENT = 'sent';
    case READ = 'read';
}
