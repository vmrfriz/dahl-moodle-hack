<?php global $USER, $THEMES, $MOODLE; ?>
<table class="table bg-light">
    <thead>
        <tr>
            <th>Название</th>
            <th>Оценка</th>
            <th>Диапазон</th>
            <th>Выполнение, %</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($THEMES as $theme): ?>
        <tr>
            <td>
                <a <?php if ($theme['grade'] != 0): ?>href="/user/<?=$USER->id ?>/tests/<?=$theme['id'] ?>/"<?php endif ?>>
                    <?=$theme['title'] ?>
                </a>
            </td>
            <td><?=$theme['grade'] ?></td>
            <td><?=$theme['range'] ?></td>
            <td><?=intval($theme['percentage']) ?>%</td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div class="my-4">
    <a href="/user/<?=$USER->id ?>/courses" class="btn btn-primary">&laquo; Курсы</a>
</div>