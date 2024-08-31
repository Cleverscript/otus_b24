<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$APPLICATION->IncludeComponent(
	'otus:clinic.list',
	'',
	[],
	$component,
	['HIDE_ICONS' => 'Y']
);