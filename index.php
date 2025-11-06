<?php
// код нужно сохранить в init.php
// задание 1
$IBLOCK_ID = 2;

AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate', 'CheckProductDateBeforeUpdate');
function CheckProductDateBeforeUpdate(&$arFields)
{
    global $APPLICATION, $IBLOCK_ID;

    if ($arFields['IBLOCK_ID'] != $IBLOCK_ID) {
        return;
    }

	$res = CIBlockElement::GetByID($arFields['ID']);
    if ($arr = $res->GetNext()) {
		$productName = $arr['NAME'] ;
        $dateCreated = strtotime($arr['DATE_CREATE']);
        $dateCurrent = time();

		$diffDays = ($dateCurrent - $dateCreated )/86400;

		if ($diffDays < 7) {
            $APPLICATION->throwException(
                "Товар $productName был создан менее одной недели назад и не может быть изменен."
            );
			
            return false; 
        }
    }
}

// задание 2
AddEventHandler('iblock', 'OnBeforeIBlockElementDelete', 'CheckProductBeforeDelete');
function CheckProductBeforeDelete($ID)
{
    global $APPLICATION, $USER, $IBLOCK_ID;

    $res = CIBlockElement::GetByID($ID);
    if ($arr = $res->GetNext()) {

        if ($arr['IBLOCK_ID'] != $IBLOCK_ID){
				return;
		}
            

        if ($arr['SHOW_COUNTER'] > 10000) {

            $APPLICATION->throwException("Нельзя удалить данный товар, так как он очень популярный на сайте");

            $eventFields = array(
                "USER_ID" => $USER->GetID(),
                "PRODUCT_NAME" => $arr['NAME'],
                "SHOW_COUNTER"=> $arr['SHOW_COUNTER']
            );

            CEvent::Send("NOTIFY_PRODUCT_DELETE", SITE_ID, $eventFields);

			return false;
        }
    }
}

/*

Чтобы администратор мог менять текст письма в админке нужно создать почтовое событие, анпример NOTIFY_PRODUCT_DELETE
и привязать к нему почтовый шаблон с телом письма :

Пользователь #USER_ID# пытается удалить популярный товар "#PRODUCT_NAME#", у которого #SHOW_COUNTER# показов на сайте.



// задание 3
-нет фильтра ACTIVE
-нет кеширования
-много SQL-запросов
-нет выборки нужных полей
-не экранируется вывод бренда
-нет проверки "на пустоту"
