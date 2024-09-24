<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyRateTable;
use Bitrix\Currency\CurrencyLangTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Query\Join;

class ExchangeRate extends CBitrixComponent
{
    public function executeComponent()
    {
        try {
            if (!Loader::includeModule('currency')) {
                throw new \RuntimeException(Loc::getMessage("T_EXEPTION_MODULE_NOT_FOUND"));
            }

            if (empty($this->arParams['CURRENCY_FROM'])) {
                throw new \RuntimeException(Loc::getMessage("T_EXEPTION_CURRENCY_FROM_FOUND"));
            }

            if ($this->startResultCache(false, [])) {
                $rows = CurrencyRateTable::query()
                    ->where('CURRENCY', $this->arParams['CURRENCY_FROM'])
                    ->where('CURRENCY_LANG.LID', LANGUAGE_ID)
                    ->where('BASE_CURRENCY_LANG.LID', LANGUAGE_ID)
                    ->setSelect([
                        'CURRENCY',
                        'DATE_RATE',
                        'BASE_CURRENCY',
                        'RATE_CNT',
                        'RATE',
                        'CURRENCY_FROM_FULL_NAME' => 'CURRENCY_LANG.FULL_NAME',
                        'CURRENCY_TO_FULL_NAME' => 'BASE_CURRENCY_LANG.FULL_NAME',
                    ])->registerRuntimeField(
                        null,
                        new ReferenceField(
                            'CURRENCY_LANG',
                            CurrencyLangTable::class,
                            Join::on('this.CURRENCY', 'ref.CURRENCY')
                        )
                    )->registerRuntimeField(
                        null,
                        new ReferenceField(
                            'BASE_CURRENCY_LANG',
                            CurrencyLangTable::class,
                            Join::on('this.BASE_CURRENCY', 'ref.CURRENCY')
                        )
                    )
                    ->exec();

                foreach ($rows as $row) {
                    $this->arResult['CURRENCY'] = $row;
                }

                $this->SetResultCacheKeys([
                    "CURRENCY",
                ]);
            }

            $this->includeComponentTemplate();

        } catch (\Throwable $e) {
            echo '<pre>';
            echo "<strong>{$e->getMessage()}</strong><br/><br/>";
            var_dump($e->getTraceAsString());
            echo '<pre>';
        }
    }
}