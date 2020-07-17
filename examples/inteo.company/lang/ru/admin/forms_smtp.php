<?
$MESS["INTEO_NO_RIGHTS"] = "Недостаточно прав для просмотра";
$MESS["INTEO_FORMS_SMTP_TAB_DEFAULT"] = "Настройки по умолчанию";
$MESS["INTEO_FORMS_SMTP_TAB_MAILING"] = "Рассылки";
$MESS["INTEO_FORMS_SMTP_TAB_SITE"] = "(#SITE_ID#) #SITE_NAME#";
$MESS["INTEO_FORMS_SMTP_TITLE_DEFAULT"] = "Настройки отправки всех писем по умолчанию";
$MESS["INTEO_FORMS_SMTP_TITLE_MAILING"] = "Настройки отправки рассылок";
$MESS["INTEO_FORMS_SMTP_TITLE_SITE"] = "Настройки отправки для сайта #SITE_NAME# (#SITE_ID#)";
$MESS["INTEO_FORMS_SMTP_HEAD"] = "Отправка писем через SMTP";
$MESS["INTEO_FORMS_SMTP_APPLY"] = "Применить";
$MESS["INTEO_FORMS_OPTIONS_USE_SMTP"] = "Использовать STMP-сервер для отправки почты";
$MESS["INTEO_FORMS_OPTIONS_LOGIN"] = "Логин (E-mail для отправки)";
$MESS["INTEO_FORMS_OPTIONS_PASSWORD"] = "Пароль";
$MESS["INTEO_FORMS_OPTIONS_SMTP_SERVER"] = "Адрес SMTP-сервера";
$MESS["INTEO_FORMS_OPTIONS_SMTP_SECURE"] = "Тип шифрования";
$MESS["INTEO_FORMS_OPTIONS_SMTP_SECURE_NO"] = "Без шифрования";
$MESS["INTEO_FORMS_OPTIONS_SMTP_PORT"] = "Порт для подключения к SMTP-серверу";
$MESS["INTEO_FORMS_OPTIONS_SMTP_CHECK"] = "";
$MESS["INTEO_FORMS_OPTIONS_SMTP_CHECK_BUTTON"] = "Проверить соединение";
$MESS["INTEO_FORMS_OPTIONS_SMTP_CHECKING"] = "Проверка";
$MESS["INTEO_FORMS_OPTIONS_SMTP_CHECKING_TIMEOUT"] = "Нет ответа от сервера. Проверьте настройки и попробуйте еще раз.";
$MESS["INTEO_FORMS_OPTIONS_SMTP_CHECKING_SUCCESS"] = "Соединение проверено. На почту, указанную в настройках главного модуля (#SITE_EMAIL#) отправлено тестовое письмо. Рекомендуем проверить, и при необходимости настроить записи SPF и DKIM для вашего домена. При неправильной настройке есть вероятность попадания писем в спам.";
$MESS["INTEO_FORMS_OPTIONS_SMTP_CHECKING_FAILED"] = "Ошибка!";
$MESS["INTEO_FORMS_OPTIONS_SMTP_TEST_DESCRIPTION"] = "Это тестовое сообщение для проверки SMTP-сервера.";
$MESS["INTEO_FORMS_OPTIONS_SMTP_TEST_TITLE"] = "Тестовое письмо";
$MESS["INTEO_FORMS_OPTIONS_SMTP_LOG"] = "Записывать ошибки в <a href=\"/bitrix/admin/event_log.php\" target=\"_blank\">журнал событий</a>";
$MESS["INTEO_FORMS_OPTIONS_CUSTOM_MAIL_ERROR"]
	= "Функция custom_mail уже используется в системе. Если вы хотите настроить отправку почты через SMTP, необходимо удалить функцию (обычно из файла /bitrix/php_interface/init.php), либо удалить модуль, использующий эту функцию.";
$MESS["INTEO_FORMS_OPTIONS_PHPMAILER_ERROR"]
	= "Класс для отправки писем (class.phpmailer.php) уже подключен в системе. По возможности удалите сторонние модули для отправки почты.";
$MESS["INTEO_FORMS_OPTIONS_USE_DEFAULT"] = "Использовать настройки по умолчанию";
$MESS["INTEO_FORMS_OPTIONS_READY"] = "Готовые настройки";
$MESS["INTEO_FORMS_OPTIONS_YANDEX"] = "Яндекс";
$MESS["INTEO_FORMS_OPTIONS_GMAIL"] = "Gmail";
$MESS["INTEO_FORMS_OPTIONS_MAILRU"] = "Mail.ru";
$MESS["INTEO_FORMS_OPTIONS_SETTINGS_8BIT"]
	= "Конвертировать 8-битные символы в заголовке письма - Да (решит возможные проблемы с кодировкой заголовка)";
$MESS["INTEO_FORMS_OPTIONS_SETTINGS_ERROR"]
	= "В настройках <a href=\"/bitrix/admin/settings.php?lang=ru&mid=main&tabControl_active_tab=tab_mail&back_url_settings=\" target=\"_blank\">главного модуля</a> рекомендуем установить следующие настройки:";
$MESS["INTEO_FORMS_OPTIONS_SETTINGS_INITPHP"] = "Для корректной работы SMTP в рассылках добавьте строку в файл init.php (обычно <a href=\"/bitrix/admin/fileman_admin.php?PAGEN_1=1&SIZEN_1=20&lang=ru&path=%2Fbitrix%2Fphp_interface&show_perms_for=0\" target=\"_blank\">/bitrix/php_interface/init.php</a>):";
