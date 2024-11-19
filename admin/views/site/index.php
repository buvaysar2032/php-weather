<?php

/**
 * @var $this         yii\web\View
 * @var $title        string
 * @var $lead         string
 * @var $vueInstalled bool
 */
?>
<div class="site-index">
    <main-jumbotron title="<?= $title ?>" lead="<?= $lead ?>">
        <?php if (!$vueInstalled): ?>
            <div class="alert alert-danger" role="alert">
                VueJs не установлен! Выполните команды <code>pnpm install</code> и <code>pnpm build</code>
            </div>
        <?php endif ?>
    </main-jumbotron>
</div>
