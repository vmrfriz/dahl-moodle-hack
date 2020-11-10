<?php global $COURSES, $USER; ?>
<div class="row">
    <div class="col-md-6">
        <div class="list-group">
        <?php foreach ($COURSES as $course): ?>
            <a href="/user/<?=$USER->id ?>/themes/<?=$course['id'] ?>" class="list-group-item list-group-item-action">
                <?=$course['title'] ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
</div>