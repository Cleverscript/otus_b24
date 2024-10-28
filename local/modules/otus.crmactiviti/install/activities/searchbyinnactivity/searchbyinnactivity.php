<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Otus\CrmActiviti\Utils\BaseUtils;
use Otus\CrmActiviti\Services\Dadata;
use Otus\CrmActiviti\Helpers\CompanyHelper;
use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class CBPSearchByInnActivity extends BaseActivity
{
    protected string $moduleId;
    protected int $orderId;

    // The first element should be the installer module avtiviti!
    protected static $requiredModules = [
        'otus.crmactiviti',
        'crm',
    ];

    public function __construct($name)
    {
        parent::__construct($name);

        $this->moduleId = current(self::$requiredModules);

        $this->arProperties = [
            'Inn' => '',
            'Text' => null, // return
        ];

        $this->SetPropertiesTypes([
            'Text' => ['Type' => FieldType::STRING],
        ]);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    protected function internalExecute(): ErrorCollection 
    {
        $companyName = null;
        $errors = parent::internalExecute();
        $this->orderId = (int) $this->ParseValue('{=Document:ID}');

        $token = Option::get($this->moduleId, 'OTUS_CRM_ACTIVITI_DADATA_TOKEN');
        $secret = Option::get($this->moduleId, 'OTUS_CRM_ACTIVITI_DADATA_SECRET');
        $propCompInnCode = Option::get($this->moduleId, 'OTUS_CRM_ACTIVITI_CRM_COMPANY_PROP_UF_INN');
        $orderPropCompanyCode = Option::get($this->moduleId, 'OTUS_CRM_ACTIVITI_IBLOCK_PROP_COMP_CODE');

        if (!$this->orderId) {
            $errors->setError(
                new \Bitrix\Main\Error(Local::getMessage('SEARCHBYINN_ACTIVITY_DOC_ID_NULL'))
            );

            return $errors;
        }

        if (empty($this->Inn)) {
            $errors->setError(
                new \Bitrix\Main\Error(Local::getMessage('SEARCHBYINN_ACTIVITY_INN_EMPTY'))
            );

            return $errors;
        }

        if (empty($token)) {
            $errors->setError(
                new \Bitrix\Main\Error(Local::getMessage('SEARCHBYINN_ACTIVITY_MOD_DADATA_TOKEN_EMPTY'))
            );

            return $errors;
        }

        if (empty($secret)) {
            $errors->setError(
                new \Bitrix\Main\Error(Local::getMessage('SEARCHBYINN_ACTIVITY_MOD_DADATA_SECRET_EMPTY'))
            );

            return $errors;
        }

        if (empty($propCompInnCode)) {
            $errors->setError(
                new \Bitrix\Main\Error(Local::getMessage('SEARCHBYINN_ACTIVITY_MOD_COMP_INN_EMPTY'))
            );

            return $errors;
        }

        if (empty($orderPropCompanyCode)) {
            $errors->setError(
                new \Bitrix\Main\Error(Local::getMessage('SEARCHBYINN_ACTIVITY_MOD_COMP_EMPTY'))
            );

            return $errors;
        }

        try {
            $dadata = new Dadata($token, $secret);
            $dadata->init();

            $fields = ['query' => $this->Inn, 'count' => 5];
            $response = $dadata->suggest("party", $fields);

            if (!empty($response['suggestions'])) {
                $companyName = $response['suggestions'][0]['value'];
            }
        } catch (\Throwable $e) {
            $this->logError($e->getMessage());
        }

        if (empty($companyName)) {
            $this->logError(
                Loc::getMessage(
                    'SEARCHBYINN_ACTIVITY_DADATA_COMPANY_EMPTY',
                    ['#INN#' => $this->Inn]
                )
            );

            return $errors;
        }

        $companyId = CompanyHelper::isExist($this->Inn, $propCompInnCode);
        $messLangCode = 'SEARCHBYINN_ACTIVITY_COMP_USE';

        if (!$companyId) {
            $companyAddResult = CompanyHelper::addCompany([
                'TITLE' => $companyName,
                'UF_INN' => $this->Inn
            ]);

            if (!$companyAddResult->isSuccess()) {
                $this->log(BaseUtils::extractErrorMessage($companyAddResult));
                return $errors;
            }

            $companyId = (int)$companyAddResult->getData()['ID'];
            $messLangCode = 'SEARCHBYINN_ACTIVITY_COMP_CREATE_SUCCESS';
        }

        if (!$companyId) {
            $this->log(BaseUtils::extractErrorMessage($companyAddResult));
            return $errors;
        }

        // Get document
        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->getDocumentId();
        $documentService = CBPRuntime::GetRuntime(true)->getDocumentService();

        $this->preparedProperties['Text'] = Loc::getMessage(
            $messLangCode,
            [
                '#ID#' => $companyId,
                '#NAME#' => $companyName,
                '#ORDER_ID#' => $this->orderId
            ]
        );

        $this->log($this->preparedProperties['Text']);

        // Set company ID & change NAME in Order
        $resultFields = [
            'NAME' => Loc::getMessage(
                'SEARCHBYINN_ACTIVITY_CHANGE_IBLOCK_ELEM_NAME',
                [
                    '#ID#' => $this->orderId,
                    '#NAME#' => $companyName
                ]
            ),
            "PROPERTY_{$orderPropCompanyCode}" => $companyId
        ];

        $documentService->UpdateDocument($documentId, $resultFields, $this->ModifiedBy);

        return $errors;
    }

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