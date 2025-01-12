<?php
namespace Otus\Autoservice\Agents;

use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Helpers\BaseHelper;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Services\PurchaseRequestService;

class ActualizeQuantityAgent
{
    use ModuleTrait;

    public static function run()
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
            pLog([
                __METHOD__ => $e->getMessage() . ': ' . $e->getTraceAsString()
            ]);
        }

        return '\Otus\Autoservice\Agents\ActualizeQuantityAgent::run();';
    }
}