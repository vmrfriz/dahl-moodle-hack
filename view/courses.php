<?php global $COURSES, $USER; ?>

<h1 class="h3 font-weight-normal">Выбор курса</h1>

<div class="d-inline-block">
    <div class="list-group pr-3">
    <?php foreach ($COURSES as $course): ?>
        <a href="/user/<?=$USER->id ?>/themes/<?=$course['id'] ?>" class="list-group-item list-group-item-action">
            <?=$course['title'] ?>
        </a>
    <?php endforeach; ?>
    </div>
</div>
