<?php
// Redirecionar para mensagem-create.php com o ID
header('Location: mensagem-create.php?id=' . ($_GET['id'] ?? ''));
exit;
