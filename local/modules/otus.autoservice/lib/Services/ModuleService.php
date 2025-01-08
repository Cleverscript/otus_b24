<?php
namespace Otus\Autoservice\Services;

use Otus\Autoservice\Traits\SingletonTrait;
use Otus\Autoservice\Traits\ModuleTrait;
use Bitrix\Main\Application;

class ModuleService
{
    use SingletonTrait;
    use ModuleTrait;

    protected array $propVals = [];

    private function __construct() {
        $this->setPropVals();
    }

    public function getPropVals(): array
    {
        return $this->propVals;
    }

    public function getPropVal(string $key): mixed
    {
        return$this->propVals[$key] ?? null;
    }

    private function setPropVals(): void
    {
        $conn = Application::getConnection();
        $this->propVals = array_column(
            $conn->query("SELECT `NAME`, `VALUE` FROM `b_option` WHERE `MODULE_ID` = '" . self::$moduleId . "'")->fetchAll(),
            'VALUE', 'NAME'
        );
    }
}

