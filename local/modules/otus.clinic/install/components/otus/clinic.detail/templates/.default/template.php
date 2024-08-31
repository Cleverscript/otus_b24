<div class="companies">
	<?php foreach ($arResult['FIELDS'] as $item => $value): ?>
		<?php if (!empty($value)): ?>
			<p><?= $arResult['NAMES'][$item] ?>: <?= $value ?></p>
		<?php endif ?>
	<?php endforeach; ?>

	<?php foreach ($arResult['PROPERTIES'] as $item => $value): ?>
		<?php if (!empty($value)): ?>
			<p><?= $arResult['NAMES'][$item] ?>: <?= $value ?></p>
		<?php endif ?>
	<?php endforeach; ?>
</div>