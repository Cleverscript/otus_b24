<?php

namespace Otus\Bookingfield\UserTypes;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;;

class BookingProcedureLink
{
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


    public static function PrepareSettings($arFields)
    {
        // return array("_BLANK" => ($arFields["USER_TYPE_SETTINGS"]["_BLANK"] == "Y" ? "Y" : "N"));
        if(is_array($arFields["USER_TYPE_SETTINGS"]) && $arFields["USER_TYPE_SETTINGS"]["_BLANK"] == "Y"){
            return array("_BLANK" =>  "Y");
        }else{
            return array("_BLANK" =>  "N");
        }
    }

   
    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $arSettings = self::PrepareSettings($arProperty);

        $arVals = array();
        if (!is_array($arProperty['VALUE'])) {
            $arProperty['VALUE'] = array($arProperty['VALUE']);
            $arProperty['DESCRIPTION'] = array($arProperty['DESCRIPTION']);
        }
        foreach ($arProperty['VALUE'] as $i => $value) {
            $arVals[$value] = $arProperty['DESCRIPTION'][$i];
        }

        $strResult = '';
        $strResult = '<a ' . ($arSettings["_BLANK"] == 'Y' ? 'target="_blank"' : '') . ' href="' . trim($arValue['VALUE']) . '">' . (trim($arVals[$arValue['VALUE']]) ? trim($arVals[$arValue['VALUE']]) : trim($arValue['VALUE'])) . '</a>';
        return $strResult;
    }


    public static function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $arSettings = self::PrepareSettings($arProperty);

        $strResult = '';
        $strResult = '<a ' . ($arSettings["_BLANK"] == 'Y' ? 'target="_blank"' : '') . ' href="' . trim($arValue['VALUE']) . '">' . (trim($arValue['DESCRIPTION']) ? trim($arValue['DESCRIPTION']) : trim($arValue['VALUE'])) . '</a>';
        return $strResult;
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

        $iblProceduresId = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_PROCEDURES');

        $iblockProcedures = Iblock::wakeUp($iblProceduresId)->getEntityDataClass();
        $rows = $iblockProcedures::query()
            ->where('ACTIVE', 'Y')
            ->setSelect(['ID', 'NAME'])
            ->exec();

        $inpid = md5('link_' . rand(0, 999));

        $value = htmlspecialcharsex($arValue['VALUE']);

        //dump([$arProperty, $arValue, $strHTMLControlName]);

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

