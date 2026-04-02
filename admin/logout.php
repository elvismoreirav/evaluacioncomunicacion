<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::logoutAdmin();
redirect('login.php');
