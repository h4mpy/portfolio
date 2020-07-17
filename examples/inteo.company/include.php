<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

if (function_exists('PHPMailerAutoload') || class_exists('PHPMailer'))
{
	$GLOBALS["INTEO_ERRORS"]["PHPMAILER"] = "Y";
}
else
{
	if (function_exists('custom_mail'))
	{
		$GLOBALS["INTEO_ERRORS"]["CUSTOMMAIL"] = "Y";
	}
	else
	{
		function custom_mail($to, $subject, $message, $additional_headers, $additional_parameters)
		{
			\Inteo\Company\Mailer::Send($to, $subject, $message, $additional_headers, $additional_parameters);
			return true;
		}
	}
}
?>