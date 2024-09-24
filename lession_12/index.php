<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->IncludeComponent(
	"otus:exchange.rate", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"CURRENCY_FROM" => "USD",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "86400"
	),
	false,
	array(
		"HIDE_ICONS" => "N"
	)
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');