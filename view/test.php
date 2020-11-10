<?php global $USER, $MOODLE, $TEST; ?>

<h2>Результаты теста: </h2>

<table class="table bg-light">
    <thead>
        <tr>
            <th>№</th>
            <th>Вопрос</th>
            <th>Ответы</th>
            <th>Оценка</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($TEST as $i => $t): ?>
    <?php if ($t['is_answered']): ?>
        <tr<?php if ($t['grade'] === $t['grade_max']): ?> class="bg-success"<?php endif ?>>
            <td><?=($i + 1) ?></td>
            <td><?=$t['question'] ?></td>
            <td>
                <ul>
                <?php foreach ($t['selected_answers'] as $answer): ?>
                    <li><?=$answer ?></li>
                <?php endforeach ?>
                </ul>
            </td>
            <td class="text-right text-nowrap"><?=$t['grade'] ?> из <?=$t['grade_max'] ?></td>
        </tr>
    <?php endif ?>
    <?php endforeach ?>
    </tbody>
</table>

<div class="my-4">
    <a href="/user/<?=$USER->id ?>/courses" class="btn btn-primary">&laquo; Курсы</a>
</div>