<?php
namespace Otus\Autoservice\Services;

use Otus\Autoservice\Tables\DepartmentTable;
use Otus\Autoservice\Services\IblockService;
use Otus\Autoservice\Helpers\BaseHelper;

class DepartmentService
{
    /**
     * Возвравщает все подразделения компании
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getAll(): array
    {

        $iblocks = IblockService::getIblocks(['IBLOCK_TYPE_ID' => 'structure', 'CODE' => 'departments']);

        if (!$iblocks->isSuccess()) {
            throw new \Exception(BaseHelper::extractErrorMessage($iblocks));
        }

        //departments
        $ibDepId = current(array_keys($iblocks->getData()));

        if (!$ibDepId) {
            return [];
        }

        $sections = IblockService::getIblockSections($ibDepId);

        if (!$sections->isSuccess()) {
            throw new \Exception(BaseHelper::extractErrorMessage($sections));
        }

        return $sections->getData();
    }
}