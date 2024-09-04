<div class="detail">
	<?php foreach ($arResult['ITEM'] as $item => $value): ?>
		<?php if (!empty($value)): $item = $item . '_ID' == 'PROCEDURES_ID' ? $item . '_ID': $item; ?>
			<p>
                <?=$arResult['NAMES'][$item] ?>:
                <strong><?php echo (is_array($value))? implode(', ', $value) : $value; ?></strong>
            </p>
		<?php endif ?>
	<?php endforeach; ?>
</div>