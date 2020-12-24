<? global $DATA, $USER, $MOODLE; ?>
<? if ($DATA['TASKS']): ?>
<details>
	<summary class="h3 font-weight-normal mb-3" style="list-style-type:'&#9776;  '">Задания</summary>
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
		<?php foreach ($DATA['TASKS'] as $item): ?>
			<tr>
				<td>
				<? if ($item['grade'] != 0): ?>
					<a href="/user/<?=$USER->id ?>/task/<?=$item['id'] ?>/">
						<?=$item['title'] ?>
					</a>
				<? else: ?>
					<?=$item['title'] ?>
				<? endif ?>
				</td>
				<td class="text-right"><?=$item['grade'] ?></td>
				<td class="text-right"><?=$item['range'] ?></td>
				<td class="text-right"><?=intval($item['percentage']) ?>%</td>
				<td class="text-right">
					<a href="/task/<?=$item['id'] ?>" class="btn btn-sm btn-outline-success" title="Поиск во всех аккаунтах" onclick="this.remove()">&#10004;</a>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</details>
<? endif ?>
