<?php global $title, $completed_tasks; ?>

<h1 class="h3 font-weight-normal mb-4 text-center">
	<?=$title; ?>
</h1>

<table class="table bg-light">
    <thead>
        <tr>
            <th>Автор</th>
            <th>Оценка</th>
            <th>Файлы</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($completed_tasks as list('user' => $user, 'task' => $task)): ?>
        <tr>
            <td><?=$user['name'] ?></td>
			<td class="text-nowrap">
			<? if (isset($task['grade'])): ?>
				<?=$task['grade'] ?>
			<? else: ?>
				не оценено
			<? endif ?>
			</td>
			<td>
				<ul class="mb-0 small">
				<? foreach ($task['files'] as $file): ?>
					<li><a href="/user/<?=$user['id'] ?>/download/?url=<?=urlencode($file['href']) ?>" target="_blank"><?=$file['title'] ?></a></li>
				<? endforeach ?>
				</ul>
			</td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
