<?php

namespace Otus\Bookingfield\UserTypes;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Otus\Bookingfield\Traits\ModuleTrait;

class BookingProcedureLink
{
    use ModuleTrait;

    public static function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE'        => 'S', // тип поля
            'USER_TYPE'            => 'iblock_booking_procedure_link', // код типа пользовательского свойства
            'DESCRIPTION'          => Loc::getMessage('OTUS_BOOKINGFIELD_BOOKING_PROPERTY_NAME'), // название типа пользовательского свойства
            'GetPropertyFieldHtml' => [self::class, 'GetPropertyFieldHtml'], // метод отображения свойства
            'GetSearchContent' => [self::class, 'GetSearchContent'], // метод поиска
            'GetAdminListViewHTML' => [self::class, 'GetAdminListViewHTML'],  // метод отображения значения в списке
            'GetPublicEditHTML' => [self::class, 'GetPropertyFieldHtml'], // метод отображения значения в форме редактирования
            'GetPublicViewHTML' => [self::class, 'GetPublicViewHTML'], // метод отображения значения
        );
    }

    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $propVal = self::preparePropVal($arValue['VALUE']);

        $strResult = "<a class=\"procedure-item-grid\"";
        $strResult .= " data-procedure-id=\"{$propVal['ID']}\"";
        $strResult .= " data-procedure-name=\"{$propVal['NAME']}\"";
        $strResult .= " data-iblock-id=\"{$propVal['IBLOCK_ID']}\"";
        $strResult .= " data-fio=\"{$propVal['FIO']}\"";
        $strResult .= " href=\"javascript:void(0);\">";
        $strResult .= "{$propVal['NAME']}";
        $strResult .= "</a>";

        return $strResult;
    }

    public static function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $propVal = self::preparePropVal($arValue['VALUE']);
        return "[{$propVal['ID']}] {$propVal['NAME']}";
    }

    public static function preparePropVal(string $val)
    {
        $iblBookingProcedureId = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING');

        $explVal = explode(';', $val);

        return [
            'ID' => current($explVal),
            'NAME' => array_pop($explVal),
            'FIO' => CurrentUser::get()->getFullName() ?: CurrentUser::get()->getLogin(),
            'IBLOCK_ID' => $iblBookingProcedureId,
        ];
    }

    public static function GetSearchContent($arProperty, $value, $strHTMLControlName)
    {
        if (trim($value['VALUE']) != '') {
            return $value['VALUE'] . ' ' . $value['DESCRIPTION'];
        }

        return '';
    }

    public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
    {
        global $bVarsFromForm, $bCopy, $PROP, $APPLICATION;

        $strResult = '';

        $propId = self::GetUserTypeDescription()['USER_TYPE'];

        if (!$propId) return $strResult;

        $iblProceduresId = Option::get(self::$moduleId, 'OTUS_BOOKINGFIELD_IBLOCK_PROCEDURES');

        if (!$iblProceduresId) {
            return $strResult;
        }

        $iblockProcedures = Iblock::wakeUp($iblProceduresId)->getEntityDataClass();

        $rows = $iblockProcedures::query()
            ->where('ACTIVE', 'Y')
            ->setSelect(['ID', 'NAME'])
            ->exec();

        $inpid = md5('link_' . rand(0, 999));

        $value = htmlspecialcharsex($arValue['VALUE']);

        $strResult .= "<select id=\"select_{$propId}_{$inpid}\">";
        $strResult .= "<option value=\"\">---</option>";

        foreach ($rows as $row) {
            $selected = explode(';', $arValue['VALUE'])[0] == $row['ID'] ? 'selected' : null;
            $strResult .= "<option value=\"{$row['ID']}\" {$selected}>[{$row['ID']}] {$row['NAME']}</option>";
        }

        $strResult .= '</select>&nbsp;';

        $strResult .= "<input id=\"val_{$propId}_{$inpid}\" type=\"hidden\" name=\"{$strHTMLControlName["VALUE"]}\" value=\"{$value}\">";

        $strResult .= "
        <script type=\"text/javascript\">
        BX.bind(
            BX('select_{$propId}_{$inpid}'),
            'bxchange',
            function()
            {
                let select = BX('select_{$propId}_{$inpid}');
                let val = select.value;
                
                let option = select.selectedOptions[0];
                let text = option.text;
                
                BX('val_{$propId}_{$inpid}').value = val + ';' + text.replace(/\[\d+\]\s/g, '');
            }
        );
        </script>";

        return $strResult;
    }
}

