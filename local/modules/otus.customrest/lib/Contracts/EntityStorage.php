<?php
namespace Otus\Customrest\Contracts;

interface EntityStorage
{
    public function add($arParams, $navStart, \CRestServer $server): int;

    public function list($arParams, $navStart, \CRestServer $server): array;

    public function update ($arParams, $navStart, \CRestServer $server): int;

    public function delete ($arParams, $navStart, \CRestServer $server): bool;
}