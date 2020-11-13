<?php global $USERS; ?>

<h1 class="h3 font-weight-normal">Выбор аккаунта</h1>

<div class="d-inline-block">
    <div class="list-group pr-3">
    <?php foreach ($USERS as $user): ?>
    <? if ($user['active']): ?>
        <a href="/user/<?=$user['id'] ?>/courses/" class="list-group-item list-group-item-action pr-5">
    <? else: ?>
        <a href="#" class="list-group-item list-group-item-action pr-5 disabled" tabindex="-1" aria-disabled="true">
    <? endif ?>
        <? if ($user['active'] && $user['token'] && (new App\Moodle($user['token']))->checkToken()): ?>
            <span class="d-inline-block rounded-circle mr-2 bg-success" style="width:12px;height:12px;cursor:default" title="Нажмите, чтобы показать токен" onclick="prompt('Токен аккаунта <?=$user['login'] ?>', '<?=$user['token'] ?>');return false"></span>
        <? else: ?>
            <span class="d-inline-block rounded-circle mr-2" style="width:12px;height:12px"></span>
        <? endif ?>
            <?=$user['name'] ?>
        </a>
    <?php endforeach; ?>
    </div>
</div>
