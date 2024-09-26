<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Application;
use Otus\Models\OrderTable;

try {
// Создаем таблицу сущности в БД
    $class = OrderTable::class;
    $connection = Application::getInstance()->getConnection();
    $entity = Base::getInstance(OrderTable::class);

    if (empty($entity->getDBTableName())) {
        throw new \RuntimeException("Ошибка получения сущности {$class}");
    }

    if ($connection->isTableExists($entity->getDBTableName())) {
        throw new \RuntimeException("Таблица {$entity->getDBTableName()} существует");
    }

    $entity->createDBTable();
    echo "Таблица {$entity->getDBTableName()} создана<br/>";

} catch (\Throwable $e) {
    echo "<strong>{$e->getMessage()}</strong>";
    echo '<pre>';
    var_dump($e->getTrace());
    echo '<pre>';
}
?>

<?php
// Получаем компании
$companys = \Bitrix\Iblock\Elements\ElementCompanyTable::query()
            ->where('ACTIVE', 'Y')
            ->setSelect(['ID', 'NAME'])
            ->exec();

// Получаем клиентов
$clients = \Bitrix\Iblock\Elements\ElementClientsTable::query()
    ->where('ACTIVE', 'Y')
    ->setSelect(['ID', 'NAME'])
    ->exec();


// Добавляем заказ
try {
    $request = Context::getCurrent()->getRequest();
    $title = $request->getPost('TITLE');
    $compatyId = $request->getPost('COMPANY_ID');
    $clientId = $request->getPost('CLIENT_ID');

    if (empty($title)) {
        throw new \InvalidArgumentException('Не указано наименование');
    }
    if (!intval($compatyId)) {
        throw new \InvalidArgumentException('Не указан ID компании');
    }
    if (!intval($compatyId)) {
        throw new \InvalidArgumentException('Не указан ID клиента');
    }

    $addResult  = OrderTable::add([
        'TITLE' => $title,
        'COMPANY_ID' => $compatyId,
        'CLIENT_ID' => $clientId
    ]);

    if (!$addResult->isSuccess()) {
        throw new \Exception(implode(', ', $addResult->getErrorMessages()));
    }

    echo "<hr/><p>Добавлен заказ с ID #{$addResult->getId()}</p>";

} catch (\Throwable $e) {
    echo "<strong style=\"color:red;\">{$e->getMessage()}</strong>";
    echo '<pre>';
    var_dump($e->getTrace());
    echo '<pre>';
}

// Получаем заказы
$orders = OrderTable::query()
    ->setSelect([
            '*',
            'COMPANY_NAME' => 'COMPANY.NAME',
            'CLIENT_NAME' => 'CLIENT.NAME',
        ])
    ->addOrder('ID', 'DESC')
    ->exec();
?>
<hr/>
<h3>Создать заказ</h3>
<form action="" method="post">
    <p>Наименование *: <input type="text" name="TITLE" value=""/></p>
    <p>Компания *:<select name="COMPANY_ID">
        <?php foreach($companys as $row): ?>
            <option value="<?=$row['ID'];?>"><?=$row['NAME'];?></option>
        <?php endforeach; ?>
        </select>
    </p>
    <p>Клиент *:<select name="CLIENT_ID">
            <?php foreach($clients as $row): ?>
                <option value="<?=$row['ID'];?>"><?=$row['NAME'];?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p><button type="submit">Создать</button> </p>
</form>

<hr/>
<h3>Заказы</h3>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td>ID заказа</td>
            <td>Наименование</td>
            <td>ID компании</td>
            <td>ID клиента</td>
            <td>Компания</td>
            <td>Клиент</td>
        </tr>
    </thead>
    <?php if(!empty($orders)): ?>
        <?php foreach($orders as $order): ?>
        <tr>
            <td><?=$order['ID'];?></td>
            <td><?=$order['TITLE'];?></td>
            <td><?=$order['COMPANY_ID'];?></td>
            <td><?=$order['CLIENT_ID'];?></td>
            <td><?=$order['COMPANY_NAME'];?></td>
            <td><?=$order['CLIENT_NAME'];?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
