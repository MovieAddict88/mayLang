<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_logout();
redirect('/');
