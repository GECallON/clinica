<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/models/Usuario.php';

$usuario = new Usuario();
$usuario->logout();
redirect('index.php');
