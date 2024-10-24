<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class СBPTestActivity extends BaseActivity
{
    // protected static $requiredModules = ["crm"];

    /**
     * @see parent::_construct()
     * @param $name string Activity name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Inn' => '',

            // return
            'Text' => null,
        ];

        $this->SetPropertiesTypes([
            'Text' => ['Type' => FieldType::STRING],
        ]);
    }

    /**
     * Return activity file path
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * @return ErrorCollection
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        $token = "0c825d0906122684951a7a3d60ee8848289d4344";
        $secret = "db2700343995d8f5e1992e0fcbd81ded70267e71";

        $dadata = new Dadata($token, $secret);
        $dadata->init();

        $fields = array("query" => $this->Inn, "count" => 5);
        $response = $dadata->suggest("party", $fields);

        $companyName = 'Компания не найдена!';
        if(!empty($response['suggestions'])){ // если копания найдена
            // по ИНН возвращается массив в котором может бытьнесколько элементов (компаний)
            $companyName = $response['suggestions'][0]['value']; // получаем имя компании из первого элемента
        }

        $this->preparedProperties['Text'] = $companyName;
        $this->log($this->preparedProperties['Text']);

        // сохранение полученных результатов работы активити в переменную бизнес процесса
        // $rootActivity = $this->GetRootActivity();
        // $rootActivity->SetVariable("TEST", $this->preparedProperties['Text']);

        // получение значения полей документа в активити
        /*$documentType = $rootActivity->getDocumentType();
        $documentId = $rootActivity->getDocumentId();
        $documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
        $documentFields =  $documentService->GetDocumentFields($documentType);

        // поле номер ИНН
        foreach ($documentFields as $key => $value) {
            if($key == 'UF_CRM_1718872462762'){ // название поля документа
                $result = $documentService->getFieldValue($documentId, $key, $documentType);
                $this->log('result'.' '.$result);
            }
        }*/

        return $errors;
    }

    /**
     * @param PropertiesDialog|null $dialog
     * @return array[]
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        $map = [
            'Inn' => [
                'Name' => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_SUBJECT'),
                'FieldName' => 'inn',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
        ];
        return $map;
    }
}