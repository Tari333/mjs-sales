<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';

session_unset();
session_destroy();

header('Location: ' . BASE_URL);
exit();