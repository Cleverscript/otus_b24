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

    /**
     * Возвращает все св-ва модуля со значениями
     *
     * @return array
     */
    public function getPropVals(): array
    {
        return $this->propVals;
    }

    /**
     * Возвращает значение опции модуля по ее коду
     *
     * @param string $key
     * @return mixed
     */
    public function getPropVal(string $key): mixed
    {
        return$this->propVals[$key] ?? null;
    }

    /**
     * Получает из базы данных все опции со сзначениями
     * и записывает из в св-во
     *
     * @return void
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    private function setPropVals(): void
    {
        $conn = Application::getConnection();
        $this->propVals = array_column(
            $conn->query("SELECT `NAME`, `VALUE` FROM `b_option` WHERE `MODULE_ID` = '" . self::$moduleId . "'")->fetchAll(),
            'VALUE', 'NAME'
        );
    }
}
