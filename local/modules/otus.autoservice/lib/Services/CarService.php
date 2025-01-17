<?php
namespace Otus\Autoservice\Services;

use Bitrix\Iblock\Iblock;
use Bitrix\Crm\ContactTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

class CarService
{
    private int $iblockId;
    private $highloadBlockService;

    use ModuleTrait;

    public function __construct()
    {
        $this->iblockId = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_IB_CARS');

        $this->highloadBlockService = new HighloadBlockService;
    }

    public function getCarIblockId(): int
    {
        return $this->iblockId;
    }

    /**
     * Возвращает общее кол-во элемнетов сущности автомобиль
     *
     * @param int $contactId
     * @return int|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCount(int $contactId)
    {
        if (!$contactId) {
            return 0;
        }

        $entity = Iblock::wakeUp($this->iblockId)->getEntityDataClass();

        return $entity::query()
            ->where('CONTACT.VALUE', $contactId)
            ->exec()
            ->getSelectedRowsCount();
    }

    /**
     * Возвращает автомобили контакта (клиента)
     *
     * @param int $contactId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCars(int $contactId, int $offset = 0, int $limit = 5): array
    {
        if (!$contactId) {
            return 0;
        }

        $result = [];

        $entity = Iblock::wakeUp($this->iblockId)->getEntityDataClass();

        $entityHLBrand = $this->highloadBlockService->getEntityHLBrand();
        $entityHLModel = $this->highloadBlockService->getEntityHLModel();
        $entityHLColor = $this->highloadBlockService->getEntityHLColor();

        $сollections = $entity::query()
            ->where('CONTACT.VALUE', $contactId)
            ->addOrder('ID', 'DESC')
            ->setSelect([
                'ID',
                'NAME',
                'BRAND',
                'MODEL',
                'COLOR',
                'RELEASE_DATE',
                'MILIAGE',
                'VIN',
                'CONTACT',
                'CONTACT_ITEM',
                'BRAND_ITEM',
                'MODEL_ITEM',
                'COLOR_ITEM'
            ])
            ->registerRuntimeField(
                new Reference(
                    'CONTACT_ITEM',
                    ContactTable::class,
                    Join::on('this.CONTACT.VALUE', 'ref.ID')
                )
            )
            ->registerRuntimeField(
                new Reference(
                    'BRAND_ITEM',
                    $entityHLBrand,
                    Join::on('this.BRAND.VALUE', 'ref.UF_XML_ID')
                )
            )
            ->registerRuntimeField(
                new Reference(
                    'MODEL_ITEM',
                    $entityHLModel,
                    Join::on('this.MODEL.VALUE', 'ref.UF_XML_ID')
                )
            )
            ->registerRuntimeField(
                new Reference(
                    'COLOR_ITEM',
                    $entityHLColor,
                    Join::on('this.COLOR.VALUE', 'ref.UF_XML_ID')
                )
            )
            ->setLimit($limit)
            ->setOffset($offset)
            ->fetchCollection();

        foreach ($сollections as $item) {
            $result[] = [
                'ID' => $item->get('ID'),
                'NAME' => $item->get('NAME'),
                'CONTACT' => $item->get('CONTACT_ITEM')->get('FULL_NAME'),
                'BRAND' => $item->get('BRAND_ITEM')->get('UF_NAME'),
                'MODEL' => $item->get('MODEL_ITEM')->get('UF_NAME'),
                'COLOR' => $item->get('COLOR_ITEM')->get('UF_NAME'),
                'RELEASE_DATE' => substr($item->get('RELEASE_DATE')->getValue(), 0, 4),
                'MILIAGE' => (int)$item->get('MILIAGE')->getValue(),
                'VIN' => $item->get('VIN')->getValue(),
                'CONTACT_ID' => $item->get('CONTACT')->getValue()
            ];
        }

        return $result;
    }

    /**
     * Возвращает название автомобиля
     *
     * @param int $id
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCarName(int $id): string
    {
        if (!$id) {
            return 0;
        }

        $entity = Iblock::wakeUp($this->iblockId)->getEntityDataClass();

        return $entity::query()
            ->where('ID', $id)
            ->addOrder('ID', 'DESC')
            ->addSelect('NAME')
            ->fetch()['NAME'];
    }

    /**
     * Проверяет есть ли в ИБ автомобиль по переданному VIN
     *
     * @param string $vin
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function isExists(string $vin): bool
    {
        $entity = Iblock::wakeUp($this->iblockId)->getEntityDataClass();

        return $entity::query()
                ->where('VIN.VALUE', $vin)
                ->exec()
                ->getSelectedRowsCount() > 0;
    }

    /**
     * Метод валидации VIN
     *
     * @param string $vin
     * @return bool
     */
    public function isValidVin(string $vin): bool
    {
        if (strlen($vin) != 17) {
            return false;
        }

        return true;
    }
}
