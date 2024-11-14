<?php
namespace Otus\Bookingfield\Controllers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Controller;
use Otus\Bookingfield\Traits\ModuleTrait;
use Otus\Bookingfield\Exceptions\ModuleException;

class BookingController extends Controller
{
    use ModuleTrait;

    public function addAction(array $fields): array
    {
        $name = $fields['name'];
        $date = $fields['date'];
        $result = ['id' => null, 'error' => null];

        $iblBookingId = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING');

        if (!$iblBookingId) {
            $result['errors'] = ModuleException::exceptionModuleOption(
                'IBLOCK_BOOKING',
                self::$reqModOpt
            );
        }

        $propBookinDateCode = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING_PROP_DATE');

        if (empty($propBookinDateCode)) {
            $result['errors'] = ModuleException::exceptionModuleOption(
                'IBLOCK_BOOKING_PROP_DATE',
                self::$reqModOpt
            );
        }

        if (!empty($result['errors'])) {
            return $result;
        }

        $el = new \CIBlockElement;

        $arLoadProductArray = [
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => $iblBookingId,
            "PROPERTY_VALUES"=> [$propBookinDateCode => $date],
            "NAME"           => $name,
            "ACTIVE"         => "Y"
        ];

        $id = $el->Add($arLoadProductArray);

        if (!$id) {
            $result['errors'] = $el->LAST_ERROR;
        }

        $result['id'] = $id;

        return $result;
    }
}