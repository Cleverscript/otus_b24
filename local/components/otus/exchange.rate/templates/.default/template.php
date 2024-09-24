<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
<?php
use Bitrix\Main\Localization\Loc;
?>
<table class="currency-list">
    <?php if (!empty($arResult['CURRENCY'])): ?>
        <tr>
            <td colspan="3">
                <?=Loc::getMessage('T_CURRENT_DATE_RATE');?>
                <strong><?=$arResult['CURRENCY']['DATE_RATE']->format("Y-m-d"); ?></strong>
            </td>
        </tr>
        <tr>
            <td><strong><?=$arResult['CURRENCY']['RATE_CNT'];?></strong> <?=$arResult['CURRENCY']['CURRENCY_FROM_FULL_NAME']; ?></td>
            <td>=</td>
            <td><strong><?=$arResult['CURRENCY']['RATE'];?></strong> <?=$arResult['CURRENCY']['CURRENCY_TO_FULL_NAME']; ?></td>
        </tr>
    <?php endif; ?>
</table>
