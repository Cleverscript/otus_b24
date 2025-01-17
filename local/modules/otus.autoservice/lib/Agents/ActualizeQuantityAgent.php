<?php
namespace Otus\Autoservice\Agents;

use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Helpers\BaseHelper;
use Otus\Autoservice\Services\LogService;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Services\PurchaseRequestService;

class ActualizeQuantityAgent
{
    use ModuleTrait;

    /**
     * Метод для агента, выполняет запрос к сервису random.org
     * для получения рандомного числа, и на основании полученного значения
     * обновляет остатки у товаров относящихся к группе "Запчасти", этим значнием,
     * или если значение равняется нолю, генерирует запрос на закупку, при добавлении
     * которого запускается бизнес процесс для списка (инфоблок тип список)
     *
     * @return string
     */
    public static function run(): string
    {
        try {
            $result = PurchaseRequestService::getRandomQty();

            if (!$result->isSuccess()) {
                throw new \Exception(BaseHelper::extractErrorMessage($result));
            }

            $randomQtyZero = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_DEBUG_RANDOM_QTY_ZERO');

            $qty = $randomQtyZero == 'Y'? 0 : $result->getData()['result'];

            $ids = PurchaseRequestService::getProductIds();

            if (!empty($ids)) {
                if ($qty === 0) {
                    PurchaseRequestService::createPurchaseRequest($ids);
                } else {
                    PurchaseRequestService::updateProductQty($qty, $ids);
                }
            }
        } catch (\Throwable $e) {
            LogService::writeSysLog(
                null,
                $e->getMessage() . ': ' . $e->getTraceAsString(),
                'OTUS_AUTOSERVICE_ACTUALIZE_QUANTITY_AGENT',
                'ERROR'
            );
        }

        return '\Otus\Autoservice\Agents\ActualizeQuantityAgent::run();';
    }
}
