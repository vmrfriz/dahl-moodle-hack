<? global $USER, $MOODLE, $TEST, $URI ?>

<h1 class="h3 font-weight-normal mb-3">
	<?=$TEST['title'] ?>
</h1>

<table class="table bg-light">
    <thead>
        <tr>
            <th>Вопрос</th>
            <th>Ответы</th>
            <th>Оценка</th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($TEST['questions'] as $i => $t): ?>
    <? if ($t['is_answered']): ?>
        <tr<? if ($t['grade'] === $t['grade_max']): ?> class="bg-success"<? endif ?>>
            <td><?=$t['question'] ?></td>
            <td>
                <ul>
                <? foreach ($t['selected_answers'] as $answer): ?>
                    <li><?=$answer ?></li>
                <? endforeach ?>
                </ul>
            </td>
            <td class="text-right text-nowrap"><?=$t['grade'] ?> из <?=$t['grade_max'] ?></td>
        </tr>
    <? endif ?>
    <? endforeach ?>
    </tbody>
</table>

<div class="my-4">
<? if ($USER->id ?? false): ?>
    <a href="/user/<?=$USER->id ?>/courses" class="btn btn-sm btn-outline-secondary pr-4">&larr; &nbsp; Курсы</a>
    <a href="/test/<?=$URI[3] ?>" class="btn btn-sm btn-outline-success ml-2" title="Поиск во всех аккаунтах" onclick="this.remove()">&#10004; Все верные</a>
<? else: ?>
    <a href="/" onclick="window.history.back();return false" class="btn btn-sm btn-outline-secondary pr-4">&larr; &nbsp; Назад</a>
<? endif; ?>
</div>
