<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::logoutEmployee();
redirect('interno.php');
