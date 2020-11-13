<? global $USER, $MOODLE, $TEST, $URI ?>

<h1 class="h3 font-weight-normal mb-4 text-center">
	<?=$TEST['title'] ?>
</h1>

<? if (strpos($_SERVER['REQUEST_URI'], '/user/') === 0): ?>
<h2 class="h3 font-weight-normal">
	<span class="h5 d-block mb-0">Результаты теста:</span>
	<?=$USER->name ?>
</h2>
<div class="mb-4">
    <a href="/test/<?=$URI[3] ?>">Выборка <span class="badge badge-success">правильных</span> ответов со всех аккаунтов</a>
</div>
<? else: ?>
<h2 class="mb-3">Наилучшие ответы со <span class="badge badge-success">всех аккаунтов</span></h2>
<? endif; ?>

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
    <a href="/user/<?=$USER->id ?>/courses" class="btn btn-primary">&laquo; Курсы</a>
</div>
