<?php

namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Services\HighloadBlockService;

Loc::loadMessages(__FILE__);

class HlblockPropertyBuildListHandler
{
    use ModuleTrait;

    const USER_TYPE = 'hlblock_property_in_list';

    public static function GetUserTypeDescription()
    {
        /**
         * USER_TYPE - код типа пользовательского свойства
         * DESCRIPTION - название типа пользовательского свойства
         * GetSettingsHTML - метод отображения настроек
         * GetPropertyFieldHtml - метод отображения свойства
         * PrepareSettings - метод подготовки настроек
         * GetAdminListViewHTML - метод отображения значения в списке
         * GetPublicViewHTML - метод отображения значения
         * GetPublicEditHTML - метод отображения значения в форме редактирования
         * GetSearchContent - метод поиска
         * GetPublicEditHTMLMulty -
         * GetAdminFilterHTML -
         * GetExtendedValue -
         * AddFilterFields -
         * GetUIFilterProperty -
         */
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => self::USER_TYPE,
            'DESCRIPTION' => Loc::getMessage('OTUS_AUTOSERVICE_HLBLOCK_PROPERTY_NAME'),
            'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
            'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
            'PrepareSettings' => array(__CLASS__, 'PrepareSettings'),
            'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
            'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML'),
            'GetPublicEditHTML' => array(__CLASS__, 'GetPublicEditHTML'),
            'GetSearchContent' => array(__CLASS__, 'GetSearchContent'),
            //'GetPublicEditHTMLMulty' => array(__CLASS__, 'GetPublicEditHTMLMulty'),
            //'GetAdminFilterHTML' => array(__CLASS__, 'GetAdminFilterHTML'),
            //'GetExtendedValue' => array(__CLASS__, 'GetExtendedValue'),
            //'AddFilterFields' => array(__CLASS__, 'AddFilterFields'),
            //'GetUIFilterProperty' => array(__CLASS__, 'GetUIFilterProperty')
        ];
    }

    public static function PrepareSettings($arProperty): array
    {
        $size = 1;
        $width = 0;
        $multiple = "N";
        $group = "N";
        $directoryTableName = '';
        $desc = "";
        $descCheck = "";
        $directoryUserPropValue = "";

        if (!empty($arProperty["USER_TYPE_SETTINGS"]) && is_array($arProperty["USER_TYPE_SETTINGS"]))
        {
            if (isset($arProperty["USER_TYPE_SETTINGS"]["size"]))
            {
                $size = (int)$arProperty["USER_TYPE_SETTINGS"]["size"];
                if ($size <= 0)
                    $size = 1;
            }

            if (isset($arProperty["USER_TYPE_SETTINGS"]["width"]))
            {
                $width = (int)$arProperty["USER_TYPE_SETTINGS"]["width"];
                if ($width < 0)
                    $width = 0;
            }

            if (isset($arProperty["USER_TYPE_SETTINGS"]["group"]) && $arProperty["USER_TYPE_SETTINGS"]["group"] === "Y")
                $group = "Y";

            if (isset($arProperty["USER_TYPE_SETTINGS"]["multiple"]) && $arProperty["USER_TYPE_SETTINGS"]["multiple"] === "Y")
                $multiple = "Y";

            if (isset($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
                $directoryTableName = (string)$arProperty["USER_TYPE_SETTINGS"]['TABLE_NAME'];

            if (isset($arProperty["USER_TYPE_SETTINGS"]["USER_PROP_VALUE"]))
                $directoryUserPropValue = (string)$arProperty["USER_TYPE_SETTINGS"]['USER_PROP_VALUE'];

            if (isset($arProperty["USER_TYPE_SETTINGS"]["DESCRIPTION_CHECK"]))
                $descCheck = (int)$arProperty["USER_TYPE_SETTINGS"]['DESCRIPTION_CHECK'];

            if(isset($arProperty["USER_TYPE_SETTINGS"]["DESCRIPTION_NAME"])){
                $desc = (string)$arProperty["USER_TYPE_SETTINGS"]['DESCRIPTION_NAME'];
                if($desc == '')
                    $desc = Loc::getMessage('DESCRIPTION_DES_VALUE');
            }

        }

        $result = array(
            'size' =>  $size,
            'width' => $width,
            'group' => $group,
            'multiple' => $multiple,
            "DESCRIPTION_NAME" => $desc,
            "DESCRIPTION_CHECK" => $descCheck,
            'TABLE_NAME' => $directoryTableName,
            'USER_PROP_VALUE' => $directoryUserPropValue
        );

        return $result;
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields): string
    {
        $settings = self::PrepareSettings($arProperty);

        $arPropertyFields = [
            "HIDE" => [
                "ROW_COUNT",
                "COL_COUNT",
                "MULTIPLE_CNT",
                "DEFAULT_VALUE",
                "WITH_DESCRIPTION"
            ]
        ];

        // check is exist col UF_XML_ID
        $arUserXmlEntity = [];
        $rsData = \CUserTypeEntity::GetList([], ["FIELD_NAME" => "UF_XML_ID"]);
        while ($arRes = $rsData->Fetch()) {
            $arUserXmlEntity[] = $arRes["ENTITY_ID"];
        }

        // check is exist col UF_NAME
        $arUserNameEntity = [];
        if (!empty($arUserXmlEntity)) {
            $rsData = \CUserTypeEntity::GetList([], array("FIELD_NAME" => "UF_NAME"));
            while ($arRes = $rsData->Fetch()) {
                $arUserNameEntity[] = $arRes["ENTITY_ID"];
            }
        }

        $arUserEntity = array_intersect($arUserXmlEntity, $arUserNameEntity);

        $hlblocks = (new HighloadBlockService)->getList();

        if (!$hlblocks->isSuccess()) {
            CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($hlblocks));
        }

        $html = '
        <tr>
            <td>' . Loc::getMessage('OTUS_AUTOSERVICE_HLBLOCK_PROP_SETTINGS_NAME') . '</td>
            <td>
              <select name="'. $strHTMLControlName["NAME"] .'[TABLE_NAME]">';

        foreach ($hlblocks->getData() as $hlblock) {
            $disabled = (!in_array("HLBLOCK_" . $hlblock["ID"], $arUserEntity))? 'disabled="disabled"' : '';
            $selected = ($settings["TABLE_NAME"] == $hlblock['TABLE_NAME']) ? ' selected' : '';

            $html .= "<option value=\"{$hlblock['TABLE_NAME']}\" {$disabled} {$selected}>{$hlblock['NAME_LANG']} [{$hlblock['NAME']}]</option>";
        }

        $html .= '</select>
            </td>
        </tr>';

        return $html;
    }

    /**
     * Метод отображения значения в публичной части
     * @param $arProperty
     * @param $arValue
     * @param $strHTMLControlName
     * @return string
     */
    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $propVal = self::preparePropVal($arValue['VALUE']);

        return $propVal['NAME'];
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

    public static function GetPublicEditHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $settings = self::PrepareSettings($arProperty);

        $strResult = '';

        $propId = self::GetUserTypeDescription()['USER_TYPE'];

        $hlblockService = new HighloadBlockService;

        $hlblock = $hlblockService->getList(['=TABLE_NAME' => $settings['TABLE_NAME']]);

        if (!$hlblock->isSuccess()) {
            return $strResult;
        }

        $hlblockId = current($hlblock->getData())['ID'];

        $hlBlockEntity = $hlblockService->getEntityHLById($hlblockId);

        $hlBlockEntityItems = $hlblockService->getItemsList($hlBlockEntity, ['UF_XML_ID', 'UF_NAME']);

        if (!$propId) {
            return $strResult;
        }

        $inpid = md5('link_' . rand(0, 999));

        $value = htmlspecialcharsex($arValue['VALUE']);

        $strResult .= "<select id=\"select_{$propId}_{$inpid}\">";
        $strResult .= "<option value=\"\">---</option>";

        foreach ($hlBlockEntityItems as $row) {
            $selected = $arValue['VALUE'] == $row['UF_XML_ID'] ? 'selected' : null;
            $strResult .= "<option value=\"{$row['UF_XML_ID']}\" {$selected}>{$row['UF_NAME']} [{$row['UF_XML_ID']}]</option>";
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
                    BX('val_{$propId}_{$inpid}').value = BX('select_{$propId}_{$inpid}').value;
                }
            );
            </script>";

        return $strResult;
    }
}

