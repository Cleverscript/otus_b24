<?php
namespace Otus\Bookingfield\Controllers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\ArgumentNullException;
use Otus\Bookingfield\Traits\ModuleTrait;
use Otus\Bookingfield\Exceptions\ModuleException;

class BookingController extends Controller
{
    use ModuleTrait;

    public function configureActions(): array
    {
        return [
            'add' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],
                'postfilters' => []
            ],
        ];
    }

    public function addAction(array $fields): ?array
    {
        try {
            if (empty($fields['fio'])) {
                throw new ArgumentNullException(
                    Loc::getMessage("OTUS_BOOKINGFIELD_ARGUMENT_NULL", ['#NAME#' => 'fio'])
                );
            }

            if (empty($fields['datetime'])) {
                throw new ArgumentNullException(
                    Loc::getMessage("OTUS_BOOKINGFIELD_ARGUMENT_NULL", ['#NAME#' => 'datetime'])
                );
            }

            if (empty($fields['procedure_id'])) {
                throw new ArgumentNullException(
                    Loc::getMessage("OTUS_BOOKINGFIELD_ARGUMENT_NULL", ['#NAME#' => 'procedure_id'])
                );
            }

            $fio = $fields['fio'];
            $datetime = $fields['datetime'];
            $procedureId = $fields['procedure_id'];

            $iblBookingId = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING');

            if (!$iblBookingId) {
                throw new SystemException(ModuleException::exceptionModuleOption(
                    'IBLOCK_BOOKING',
                    self::$reqModOpt
                ));
            }

            $propBookinDateCode = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING_PROP_DATE');

            if (empty($propBookinDateCode)) {
                throw new SystemException(
                    ModuleException::exceptionModuleOption(
                        'IBLOCK_BOOKING_PROP_DATE',
                        self::$reqModOpt
                    )
                );
            }

            $propBookinProcedureCode = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING_PROP_PROCEDURE');

            if (empty($propBookinProcedureCode)) {
                throw new SystemException(
                    ModuleException::exceptionModuleOption(
                        'IBLOCK_BOOKING_PROP_PROCEDURE',
                        self::$reqModOpt
                    )
                );
            }

            $el = new \CIBlockElement;

            $id = $el->Add([
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => $iblBookingId,
                "PROPERTY_VALUES" => [
                    $propBookinDateCode => $datetime,
                    $propBookinProcedureCode => $procedureId
                ],
                "NAME" => $fio,
                "ACTIVE" => "Y"
            ]);

            if (!$id) {
                throw new SystemException($el->LAST_ERROR);
            }

            return ['id' => $id];

        } catch (\Throwable $e) {
            $this->addError($this->buildErrorFromException($e));
            return null;
        }
    }
}