<?php global $USERS; ?>
<table class="table bg-light">
    <thead>
        <tr>
            <th>Владелец</th>
            <th>Логин</th>
            <th>Токен</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($USERS as $user): ?>
        <tr>
            <?php $active = (new App\Moodle($user['token']))->checkToken(); ?>
            <td><?=$user['name'] ?></td>
            <td><?=$user['login'] ?></td>
            <td>
                <div style="
                    background-color: <?php if ($active): ?>green<?php else: ?>red<?php endif ?>;
                    border-radius: 100px;
                    width: .8em;
                    height: .8em;
                    display: inline-block;
                    margin-right: 10px;
                "></div>
                <?=$user['token'] ?>
            </td>
            <td><a href="/user/<?=$user['id'] ?>/courses/">Курсы</a></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>