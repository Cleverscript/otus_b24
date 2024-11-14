<?php
namespace Otus\Bookingfield\Controllers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Controller;
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
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                ],
                '-prefilters' => [
                    ActionFilter\Csrf::class,
                ],
            ],
        ];
    }

    public function addAction(array $fields): ?array
    {
        try {
            if (empty($fields['name'])) {
                throw new ArgumentNullException(
                    Loc::getMessage("OTUS_BOOKINGFIELD_ARGUMENT_NULL", ['#NAME#' => 'name'])
                );
            }

            if (empty($fields['date'])) {
                throw new ArgumentNullException(
                    Loc::getMessage("OTUS_BOOKINGFIELD_ARGUMENT_NULL", ['#NAME#' => 'date'])
                );
            }

            $name = $fields['name'];
            $date = DateTime::createFromTimestamp(strtotime($fields['date']))->format("d.m.Y h:m:s");

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

            $el = new \CIBlockElement;

            $id = $el->Add([
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => $iblBookingId,
                "PROPERTY_VALUES" => [$propBookinDateCode => $date],
                "NAME" => $name,
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