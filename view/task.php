<?php global $TASK, $USER, $URI; ?>

<h1 class="h3 font-weight-normal mb-3">
	<? if (isset($TASK['grade'])): ?>
	<span class="badge badge-info mr-2" title="Оценка"><?=$TASK['grade'] ?></span>
	<? else: ?>
	<span class="badge badge-warning mr-2" title="Оценка">не оценено</span>
	<? endif ?>
	<?=$TASK['title']; ?>
</h1>

<table class="table bg-light">
    <thead>
        <tr>
            <th>Файл</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($TASK['files'] as $file): ?>
        <tr>
            <td>
				<a href="/user/<?=$USER->id ?>/download/?url=<?=urlencode($file['href']) ?>" target="_blank"><?=$file['title'] ?></a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div class="my-4">
    <a href="/user/<?=$USER->id ?>/courses" class="btn btn-sm btn-outline-secondary pr-4">&larr; &nbsp; Курсы</a>
    <a href="/task/<?=$URI[3] ?>" class="btn btn-sm btn-outline-success ml-2" title="Поиск во всех аккаунтах">&#10004; Все выполненные</a>
</div>
