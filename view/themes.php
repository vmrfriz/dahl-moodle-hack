<?php global $USER, $THEMES, $MOODLE; ?>
<table class="table bg-light">
    <thead>
        <tr>
            <th>Название</th>
            <th>Оценка</th>
            <th>Диапазон</th>
            <th>Выполнение, %</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($THEMES as $theme): ?>
        <tr>
            <td>
                <a <?php if ($theme['grade'] != 0): ?>href="/user/<?=$USER->id ?>/test/<?=$theme['id'] ?>/"<?php endif ?>>
                    <?=$theme['title'] ?>
                </a>
            </td>
            <td class="text-right"><?=$theme['grade'] ?></td>
            <td class="text-right"><?=$theme['range'] ?></td>
            <td class="text-right"><?=intval($theme['percentage']) ?>%</td>
            <td class="text-right">
                <a href="/test/<?=$theme['id'] ?>" class="btn btn-sm btn-outline-success" title="Лучшие ответы">&#10004;</a>
                <a href="/completed-tests/<?=$theme['id'] ?>" class="btn btn-sm btn-outline-secondary ml-1" title="Найти выполненные">&#128270;</a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div class="my-4">
    <a href="/user/<?=$USER->id ?>/courses" class="btn btn-primary">&laquo; Курсы</a>
</div>