<? global $DATA, $USER, $MOODLE; ?>
<? if ($DATA['TESTS']): ?>
<details>
	<summary class="h3 font-weight-normal mb-3" style="list-style-type:'&#9776;  '">Тесты</summary>
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
		<?php foreach ($DATA['TESTS'] as $item): ?>
			<tr>
				<td>
				<?php if ($item['grade'] != 0): ?>
					<a href="/user/<?=$USER->id ?>/test/<?=$item['id'] ?>/">
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
					<a href="/test/<?=$item['id'] ?>" class="btn btn-sm btn-outline-success" title="Лучшие ответы">&#10004;</a>
					<a href="/completed-tests/<?=$item['id'] ?>" class="btn btn-sm btn-outline-secondary ml-1" title="Найти выполненные">&#128270;</a>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</details>
<? endif ?>
