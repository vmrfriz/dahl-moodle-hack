<?php global $USER; ?>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Владелец</th>
            <th>Логин</th>
            <th>Токен</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?=$USER->id ?></td>
            <td>
                <div style="
                    background-color: <?php if ($USER->active): ?>green<?php else: ?>red<?php endif ?>;
                    border-radius: 100px;
                    width: .8em;
                    height: .8em;
                    display: inline-block;
                    margin-right: 10px;
                "></div>
                <?=$USER->name ?>
            </td>
            <td><?=$USER->login ?></td>
            <td><?=$USER->token ?></td>
        </tr>
    </tbody>
</table>
<div class="my-4">
    <a href="/" class="btn btn-primary">&laquo; На главную</a>
</div>