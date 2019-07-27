<?php
$this->title = 'Profile';
?>

<p>id: <?= $id ?>, name: <?= $name ?>, age: <?= $age ?></p>
<p>friends:</p>
<ul>
    <?php foreach($friends as $name): ?>
        <li><?= $name ?></li>
    <?php endforeach; ?>
</ul>
