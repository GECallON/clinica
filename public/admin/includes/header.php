<header class="app-header">
    <div class="app-header__brand">
        <div class="app-header__logo">
            <i class="fas fa-shield-heart"></i>
        </div>
        <div>
            <p class="text-xs font-semibold tracking-[0.28em] uppercase text-slate-400">MedAgenda Pro</p>
            <h1 class="text-xl font-semibold text-slate-900">Central Administrativa</h1>
        </div>
    </div>

    <div class="flex items-center gap-5">
        <div class="app-header__user">
            <div class="app-header__user-avatar">
                <?= strtoupper(substr($_SESSION['nome'], 0, 1)) ?>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['nome']) ?></p>
                <p class="text-xs text-slate-500">Administrador</p>
            </div>
        </div>

        <a href="../logout.php" class="btn-muted flex items-center gap-2">
            <i class="fas fa-arrow-right-from-bracket text-slate-500"></i>
            Sair
        </a>
    </div>
</header>
