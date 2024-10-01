<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>
<?php
$APPLICATION->IncludeComponent(
	"otus:otus.customtab_grid", 
	".default", 
	array(
		"SET_PAGE_TITLE" => "N",
		"COMPONENT_TEMPLATE" => ".default",
		"SHOW_ROW_CHECKBOXES" => "N",
		"NUM_PAGE" => "5",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "86400"
	),
	false
);
?>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>