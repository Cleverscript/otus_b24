<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CmpGridComplex extends CBitrixComponent
{
    public array $arVariables = [];
    protected array $arUrlTemplates  = [];
    protected array $arDefaultVariableAliases  = [];
    protected array $arComponentVariables = ['CODE', 'ID'];
    public array $arDefaultUrlTemplates404 = [
        "detail" => "#ID#/",
    ];

    public function onPrepareComponentParams($arParams) 
    {
		$result = [
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => isset($arParams["CACHE_TIME"])? $arParams["CACHE_TIME"]: 36000000,
        ];

        $this->arParams = array_merge($result, $arParams);

		return $this->arParams;
	}

    public function getTemplateNameDefault()
	{
		if ($name = $this->getTemplateName()) {
			return $name;
        } 

        return '.default';
	}

	public function executeComponent() 
    {
        try {

            \Bitrix\Main\UI\Extension::load('otus.clinic');

            /*debug([
                $this->arParams['SEF_MODE'],
                $this->arDefaultUrlTemplates404,
                $arParams['SEF_URL_TEMPLATES']
            ]);*/

            if ($this->arParams['SEF_MODE'] == 'Y') {
                if (!is_array($this->arParams['SEF_URL_TEMPLATES'])) {
                    $this->arParams['SEF_URL_TEMPLATES'] = [];
                }

                $this->arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates(
                    $this->arDefaultUrlTemplates404,
                    $this->arParams['SEF_URL_TEMPLATES']
                );

                /**
                 * $this->arVariables передается по ссылке
                 * и будет содержать массив с ключем = имени переменной из шаблона пути
                */
                $view = CComponentEngine::parseComponentPath(
                    $this->arParams['SEF_FOLDER'],
                    $this->arUrlTemplates,
                    $this->arVariables,
                );
        
                $view = (!empty($view))? $view: 'list';

                $this->arResult = [
                    'SEF_FOLDER' => $this->arParams['SEF_FOLDER'],
                    'URL_TEMPLATES' => [
                        'DETAIL' => $this->arParams['SEF_FOLDER'] . $this->arDefaultUrlTemplates404['detail']
                    ]
                ];

            } else {
                $this->arVariableAliases = CComponentEngine::MakeComponentVariableAliases(
                    $this->arDefaultVariableAliases,
                    $this->arParams['VARIABLE_ALIASES']
                );
                
                CComponentEngine::InitComponentVariables(
                    false,
                    $this->arComponentVariables,
                    $this->arVariableAliases,
                    $this->arVariables
                );

                $view = (intval($this->arVariables['ID']) > 0)? 'detail' : 'list';
            }

            //debug([$view => $this->arVariables]);

            // Include template
            $this->includeComponentTemplate($view);
            
        } catch (\Throwable $e) {
            echo '<pre>';
            echo "<strong>{$e->getMessage()}</strong><br/><br/>";
            var_dump($e->getTraceAsString());
            echo '<pre>';
        }
	}
}