<?php global $users_complete_test, $URI; ?>

<? if ($users_complete_test): ?>
<h1>
    Тест выполнили
    <a href="/test/<?=$URI[1] ?>" class="btn btn-sm btn-outline-success" title="Лучшие ответы">&#10004;</a>
</h1>

<div class="row">
    <div class="col-md-6">
        <div class="list-group">
        <?php foreach ($users_complete_test as $user_id => $user_name): ?>
            <a href="/user/<?=$user_id ?>/test/<?=$URI[1] ?>" class="list-group-item list-group-item-action">
                <?=$user_name ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<? else: ?>
<h3>Этот тест не выполнил никто ¯\_(ツ)_/¯</h3>
<? endif ?>
