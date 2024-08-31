<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = [
	"NAME" => GetMessage("T_GRID"),
	"DESCRIPTION" => GetMessage("T_GRID_DESC"),
	"ICON" => "/images/news_list.gif",
	"SORT" => 1,
	"CACHE_PATH" => "Y",
	"PATH" => [
		"ID" => "Otus",
		"CHILD" => [
			"ID" => "grid",
			"NAME" => GetMessage("T_GRID"),
			"SORT" => 10,
			"CHILD" => [
				"ID" => "grid_cmpx",
            ],
        ],
    ],
];