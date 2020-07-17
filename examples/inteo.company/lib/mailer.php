<?
namespace Inteo\Company;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use PHPMailer\PHPMailer\PHPMailer;
use Email\Parse;

require $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/inteo.company/vendor/autoload.php';

Loc::loadMessages(__FILE__);

class Mailer
{
	const MODULE_ID = "inteo.company";
	private static $siteId = SITE_ID;
	private static $languageId = LANGUAGE_ID;
	private static $siteCharset = SITE_CHARSET;
	private $charset = SITE_CHARSET;
	private $arHeader = array();
	private $arHeaderLines = array();
	private $content_type;
	private $filename;
	private $boundary;
	private $bMultipart = false;
	private $MultipartType = "mixed";
	private $content_id = '';
	private $strHeader = "";

	public static function Send($to, $subject, $message, $additional_headers = null, $additional_parameters = null)
	{
		$moduleId = self::MODULE_ID;
		$isSmtp = false;
		$smtpSettings = array();
		$siteName = '';
		$checkSites = SiteTable::getList(array('filter' => array('LID' => self::$siteId), 'select' => array('LID', 'SITE_NAME', 'EMAIL')));
		if (stripos($additional_headers, 'x-bitrix-posting') !== false || stripos($additional_headers, 'precedence: bulk') !== false)
		{
			//Bitrix sender module
			if (Option::get($moduleId, "use_default_mailing", "Y", "") == 'N')
			{
				if (Option::get($moduleId, "use_smtp_mailing", "N", "") == 'Y')
				{
					$isSmtp = true;
					$smtpSettings = array(
						'login' => Option::get($moduleId, "smtp_login_mailing", "", ""),
						'password' => Option::get($moduleId, "smtp_password_mailing", "", ""),
						'server' => Option::get($moduleId, "smtp_server_mailing", "", ""),
						'port' => Option::get($moduleId, "smtp_port_mailing", "", ""),
						'secure' => Option::get($moduleId, "smtp_secure_mailing", "", ""),
						'log' => Option::get($moduleId, "smtp_log_mailing", "", ""),
					);
				}
				else
				{
					return @mail($to, $subject, $message, $additional_headers, $additional_parameters);
				}
			}
		}
		else
		{
			if ($checkSite = $checkSites->fetch()) {
				$siteName = $checkSite["SITE_NAME"];
				if (Option::get($moduleId, "use_default", "Y", self::$siteId) == 'N')
				{
					if (Option::get($moduleId, "use_smtp", "N", self::$siteId) == 'Y')
					{
						$isSmtp = true;
						$smtpSettings = array(
							'login' => Option::get($moduleId, "smtp_login", "", self::$siteId),
							'password' => Option::get($moduleId, "smtp_password", "", self::$siteId),
							'server' => Option::get($moduleId, "smtp_server", "", self::$siteId),
							'port' => Option::get($moduleId, "smtp_port", "", self::$siteId),
							'secure' => Option::get($moduleId, "smtp_secure", "", self::$siteId),
							'log' => Option::get($moduleId, "smtp_log", "", self::$siteId),
						);
					}
					else
					{
						return @mail($to, $subject, $message, $additional_headers, $additional_parameters);
					}
				}
			}
		}
		if (!$isSmtp && Option::get($moduleId, "use_smtp", "N", "") == 'Y')
		{
			$isSmtp = true;
			$smtpSettings = array(
				'login' => Option::get($moduleId, "smtp_login", "", ""),
				'password' => Option::get($moduleId, "smtp_password", "", ""),
				'server' => Option::get($moduleId, "smtp_server", "", ""),
				'port' => Option::get($moduleId, "smtp_port", "", ""),
				'secure' => Option::get($moduleId, "smtp_secure", "", ""),
				'log' => Option::get($moduleId, "smtp_log", "", ""),
			);
		}

		if ($isSmtp)
		{
			$eol = self::getMailEol();
			$arHeaders = explode($eol, $additional_headers);
			$arHeaders[] = 'Subject: ' . $subject;
			list($header, $html, $text, $attachments) = self::parseMessage(implode("\r\n",$arHeaders). "\r\n\r\n" .str_replace(PHP_EOL, "\r\n", $message), self::$siteCharset);
			$result = Parse::getInstance()->parse($to, true, self::$siteCharset);
			$arTo = array();
			if (is_array($result['email_addresses']) && count($result['email_addresses']) > 0)
			{
				foreach ($result['email_addresses'] as $emailAddress)
				{
					if ($emailAddress['invalid'] === false)
					{
						$arTo[] = array(
							'name' => (isset($emailAddress['name_parsed']))?$emailAddress['name_parsed']:'',
							'email' => (isset($emailAddress['simple_address']))?$emailAddress['simple_address']:''
						);
					}
					else
					{
						self::LogMailer(Loc::getMessage("INTEO_COMPANY_MAILER_ERROR", array("#HEADER#" => $header->GetHeader("SUBJECT"))).Loc::getMessage("INTEO_COMPANY_MAILER_ERROR_TO", array("#EMAIL#" => $emailAddress['original_address'])),Loc::getMessage("INTEO_SMTP_ERROR"));
					}
				}
			}
			if (count($arTo) > 0)
			{
				$mail = new PHPMailer;
				foreach ($arTo as $addTo)
				{
					$mail->AddAddress($addTo['email'], $addTo['name']);
				}
				$mail->isSMTP();
				$mail->SMTPAuth = ($smtpSettings['password']!='')?true:false;
				$mail->Host = $smtpSettings['server'];
				$mail->SMTPSecure = $smtpSettings['secure'];
				$mail->Port = $smtpSettings['port'];
				$mail->Username = $smtpSettings['login'];
				$mail->Password = $smtpSettings['password'];
				$fromEmail = $fromName = $smtpSettings['login'];
				$arFrom = Parse::getInstance()->parse($header->GetHeader("FROM"), false, self::$siteCharset);
				if ($fromEmail == '')
				{
					$fromEmail = $arFrom['simple_address'];
				}
				if (strlen($arFrom['name_parsed']) > 0)
				{
					$fromName = $arFrom['name_parsed'];
				}
				else
				{
					$fromName = $siteName;
				}
				$mail->SetFrom($fromEmail, $fromName);
				//$mail->setLanguage(self::$languageId);
				$mail->Subject = $header->GetHeader("SUBJECT");
				$mail->CharSet = self::$siteCharset;
				$arHeaders =  $header->GetHeaders();
				unset(
					$arHeaders['FROM'],
					$arHeaders['X-EVENT_NAME'],
					$arHeaders['DATE'],
					$arHeaders['MIME-VERSION'],
					$arHeaders['CONTENT-TYPE'],
					$arHeaders['CONTENT-TRANSFER-ENCODING'],
					$arHeaders['SUBJECT']
				);
				$mail = self::setCustomHeaders($mail, $arHeaders);
				$htmlMessage = $html;
				$textMessage = $text;
				if ($htmlMessage != '')
				{
					$mail->isHTML(true);
					$mail->Body = $htmlMessage;
					if ($textMessage !== null)
					{
						$mail->AltBody = $textMessage;
					}
				}
				else
				{
					$mail->Body = $textMessage;
				}
				if (is_array($attachments) && count($attachments) > 0)
				{
					foreach ($attachments as $attachment)
					{
						if (isset($attachment["CONTENT-ID"]) && $arFile = \CFile::GetFileArray($attachment["CONTENT-ID"]))
						{
							$mail->addAttachment($_SERVER["DOCUMENT_ROOT"].$arFile["SRC"], $arFile["ORIGINAL_NAME"]);
						}
						else
						{
							$mail->addStringAttachment($attachment['BODY'], $attachment['FILENAME']);
						}
					}
				}

				if (!$mail->send())
				{
					if ($smtpSettings['log'] == 'Y')
					{
						self::LogMailer($mail->ErrorInfo . " line:" . __LINE__, Loc::getMessage("INTEO_SMTP_ERROR"));
					}

					$remail = new PHPMailer;
					foreach ($arTo as $addTo)
					{
						$remail->AddAddress($addTo['email'], $addTo['name']);
					}
					$remail->SetFrom($fromEmail, $fromName);
					//$remail->setLanguage(self::$languageId);
					$remail->Subject = $header->GetHeader("SUBJECT");
					$remail->CharSet = self::$siteCharset;
					$remail = self::setCustomHeaders($remail, $arHeaders);
					$remail->isHTML(true);
					$setMessage = ($htmlMessage != '')?$htmlMessage:$textMessage;
					if ($to == $fromEmail)
					{
						$wrapper = '<br><br>';
						$errorText = Loc::getMessage("INTEO_COMPANY_SMTP_ERROR");
						$setMessage .= $wrapper.$errorText.$wrapper.$mail->ErrorInfo;
					}
					$remail->Body = $setMessage;

					if (is_array($attachments) && count($attachments) > 0)
					{
						foreach ($attachments as $attachment)
						{
							if (isset($attachment["CONTENT-ID"]) && $arFile = \CFile::GetFileArray($attachment["CONTENT-ID"]))
							{
								$remail->addAttachment($_SERVER["DOCUMENT_ROOT"].$arFile["SRC"], $arFile["ORIGINAL_NAME"]);
							}
							else
							{
								$remail->addStringAttachment($attachment['BODY'], $attachment['FILENAME']);
							}
						}
					}
					$remail->send();
				}
			}
			else
			{
				self::LogMailer(Loc::getMessage("INTEO_COMPANY_MAILER_ERROR", array("#HEADER#" => $header->GetHeader("SUBJECT"))).Loc::getMessage("INTEO_COMPANY_MAILER_ERROR_NORECIPIENTS"), Loc::getMessage("INTEO_SMTP_ERROR"));
			}
		}
		else
		{
			return @mail($to, $subject, $message, $additional_headers, $additional_parameters);
		}
	}

	private static function setCustomHeaders(PHPMailer $mail , $headers)
	{
		if (is_array($headers))
		{
			foreach ($headers as $key => $value)
			{
				if (strtolower($key) == 'bcc')
				{
					$arBcc = explode(',', $value);
					foreach ($arBcc as $addBcc)
					{
						$mail->addBCC(trim($addBcc));
					}
				}
				elseif (strtolower($key) == 'cc')
				{
					$arCc = explode(',', $value);
					foreach ($arCc as $addCc)
					{
						$mail->addCC(trim($addCc));
					}
				}
				elseif (strtolower($key) == 'reply-to')
				{
					$mail->addReplyTo($value);
				}
				elseif (strtolower($key) == 'x-priority')
				{
					$mail->Priority = intval($value);
				}
				else
				{
					$mail->AddCustomHeader($key, $value);
				}
			}
		}
		return $mail;
	}

	private static function parseMessage($message, $charset)
	{
		$headerP = \CUtil::binStrpos($message, "\r\n\r\n");

		if (false === $headerP)
		{
			$rawHeader = '';
			$body      = $message;
		}
		else
		{
			$rawHeader = \CUtil::binSubstr($message, 0, $headerP);
			$body      = \CUtil::binSubstr($message, $headerP+4);
		}

		$header = self::parseHeader($rawHeader, $charset);

		$htmlBody = '';
		$textBody = '';

		$parts = array();

		if ($header->isMultipart())
		{
			$startP = 0;
			$startRegex = sprintf('/(^|\r\n)--%s\r\n/', preg_quote($header->getBoundary(), '/'));
			if (preg_match($startRegex, $body, $matches, PREG_OFFSET_CAPTURE))
			{
				$startP = $matches[0][1] + \CUtil::binStrlen($matches[0][0]);
			}

			$endP = \CUtil::binStrlen($body);
			$endRegex = sprintf('/\r\n--%s--(\r\n|$)/', preg_quote($header->getBoundary(), '/'));
			if (preg_match($endRegex, $body, $matches, PREG_OFFSET_CAPTURE))
			{
				$endP = $matches[0][1];
			}

			if (!($startP < $endP))
			{
				$startP = 0;
			}

			$data = \CUtil::binSubstr($body, $startP, $endP-$startP);

			$isHtml = false;
			$rawParts = preg_split(sprintf('/\r\n--%s\r\n/', preg_quote($header->getBoundary(), '/')), $data);
			$tmpParts = array();
			foreach ($rawParts as $part)
			{
				if (\CUtil::binSubstr($part, 0, 2) == "\r\n")
					$part = "\r\n" . $part;

				list(, $subHtml, $subText, $subParts) = self::parseMessage($part, $charset);

				if ($subHtml)
					$isHtml = true;

				if ($subText)
					$tmpParts[] = array($subHtml, $subText);

				$parts = array_merge($parts, $subParts);
			}

			if (strtolower($header->MultipartType()) == 'alternative')
			{
				$candidate = '';

				foreach ($tmpParts as $part)
				{
					if ($part[0])
					{
						if (!$htmlBody || (strlen($htmlBody) < strlen($part[0])))
						{
							$htmlBody  = $part[0];
							$candidate = $part[1];
						}
					}
					else
					{
						if (!$textBody || strlen($textBody) < strlen($part[1]))
							$textBody = $part[1];
					}
				}

				if (!trim($textBody))
					$textBody = $candidate;
			}
			else
			{
				foreach ($tmpParts as $part)
				{
					if ($textBody)
						$textBody .= "\r\n\r\n";
					$textBody .= $part[1];

					if ($isHtml)
					{
						if ($htmlBody)
							$htmlBody .= "\r\n\r\n";

						$htmlBody .= $part[0] ?: $part[1];
					}
				}
			}
		}
		else
		{
			$bodyPart = self::decodeMessageBody($header, $body, $charset);

			if (!$bodyPart['FILENAME'] && strpos(strtolower($bodyPart['CONTENT-TYPE']), 'text/') === 0)
			{
				if (strtolower($bodyPart['CONTENT-TYPE']) == 'text/html')
				{
					$htmlBody = $bodyPart['BODY'];
					$textBody = html_entity_decode(htmlToTxt($bodyPart['BODY']), ENT_QUOTES | ENT_HTML401, $charset);
				}
				else
				{
					$textBody = $bodyPart['BODY'];
				}
			}
			else
			{
				$parts[] = $bodyPart;
			}
		}

		return array($header, $htmlBody, $textBody, $parts);
	}

	private static function decodeMessageBody(Mailer $header, $body, $charset)
	{
		$encoding = strtolower($header->GetHeader('CONTENT-TRANSFER-ENCODING'));

		if ($encoding == 'base64')
			$body = base64_decode($body);
		elseif ($encoding == 'quoted-printable')
			$body = quoted_printable_decode($body);
		elseif ($encoding == 'x-uue')
			$body = self::uue_decode($body);

		$content_type = strtolower($header->content_type);
		if (empty($header->filename) && !empty($header->charset))
		{
			if (preg_match('/plain|html|text/', $content_type) && !preg_match('/x-vcard|csv/', $content_type))
			{
				$body = self::convertCharset($body, $header->charset, $charset);
			}
		}

		return array(
			'CONTENT-TYPE' => $content_type,
			'CONTENT-ID'   => $header->content_id,
			'BODY'         => $body,
			'FILENAME'     => $header->filename
		);
	}

	private static function uue_decode($str)
	{
		preg_match("/begin [0-7]{3} .+?\r?\n(.+)?\r?\nend/i", $str, $reg);

		$str = $reg[1];
		$res = '';
		$str = preg_split("/\r?\n/", trim($str));
		$strlen = count($str);

		for ($i = 0; $i < $strlen; $i++)
		{
			$pos = 1;
			$d = 0;
			$len= (int)(((ord(substr($str[$i],0,1)) -32) - ' ') & 077);

			while (($d + 3 <= $len) AND ($pos + 4 <= strlen($str[$i])))
			{
				$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
				$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
				$c3 = (ord(substr($str[$i],$pos+3,1)) ^ 0x20);
				$res .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4)).
					chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2)).
					chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077));

				$pos += 4;
				$d += 3;
			}

			if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i])))
			{
				$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
				$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
				$res .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4)).
					chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

				$pos += 3;
				$d += 2;
			}

			if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i])))
			{
				$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
				$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$res .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
			}
		}

		return $res;
	}

	private static function DecodeHeader($str, $charset_to, $charset_document)
	{
		while(preg_match('/(=\?[^?]+\?(Q|B)\?[^?]*\?=)(\s)+=\?/i', $str))
			$str = preg_replace('/(=\?[^?]+\?(Q|B)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $str);
		if(!preg_match("'=\?(.*)\?(B|Q)\?(.*)\?='i", $str))
		{
			if(strlen($charset_document)>0 && $charset_document!=$charset_to)
				$str = self::ConvertCharset($str, $charset_document, $charset_to);
		}
		else
		{
			$str = preg_replace_callback(
				"'=\?(.*?)\?(B|Q)\?(.*?)\?='i",
				create_function('$m', "return \Inteo\Company\Mailer::ConvertHeader(\$m[1], \$m[2], \$m[3], '".AddSlashes($charset_to)."');"),
				$str
			);
		}

		return $str;
	}

	private static function convertCharset($str, $from, $to)
	{
		if (!trim($str))
			return $str;

		$from = trim(strtolower($from));
		$to   = trim(strtolower($to));

		$escape = function ($matches)
		{
			return isset($matches[2]) ? '?' : $matches[1];
		};

		if ($from != $to)
		{
			if (in_array($from, array('utf-8', 'utf8')))
			{
				// escape all invalid (rfc-3629) utf-8 characters
				$str = preg_replace_callback('/
					([\x00-\x7F]+
						|[\xC2-\xDF][\x80-\xBF]
						|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]
						|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})
					|([\x80-\xFF])
				/x', $escape, $str);
			}

			if ($result = \Bitrix\Main\Text\Encoding::convertEncoding($str, $from, $to, $error))
				$str = $result;
			else
				self::LogMailer(sprintf('Failed to convert email part. (%s -> %s : %s)', $from, $to, $error));
		}

		if (in_array($to, array('utf-8', 'utf8')))
		{
			// escape invalid (rfc-3629) and 4-bytes utf-8 characters
			$str = preg_replace_callback('/
				([\x00-\x7F]+
					|[\xC2-\xDF][\x80-\xBF]
					|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF])
				|([\x80-\xFF])
			/x', $escape, $str);
		}

		return $str;
	}

	public static function ConvertHeader($encoding, $type, $str, $charset)
	{
		if(strtoupper($type)=="B")
			$str = base64_decode($str);
		else
			$str = quoted_printable_decode(str_replace("_", " ", $str));

		$str = self::ConvertCharset($str, $encoding, $charset);

		return $str;
	}

	private static function parseHeader($message_header, $charset)
	{
		$h = new Mailer();
		$h->Headers($message_header, $charset);
		return $h;
	}

	private static function getMailEol()
	{
		static $eol = false;
		if($eol !== false)
			return $eol;

		if(strtoupper(substr(PHP_OS,0,3)) == 'WIN')
			$eol="\r\n";
		elseif(strtoupper(substr(PHP_OS,0,3)) <> 'MAC')
			$eol="\n"; 	 //unix
		else
			$eol="\r";

		return $eol;
	}

	public function __construct()
	{
	}

	private function GetHeaders()
	{
		return $this->arHeader;
	}

	private function GetHeader($type)
	{
		return $this->arHeader[strtoupper($type)];
	}

	private function IsMultipart()
	{
		return $this->bMultipart;
	}

	private function GetBoundary()
	{
		return $this->boundary;
	}

	private function MultipartType()
	{
		return strtolower($this->MultipartType);
	}

	private function Headers($message_header, $charset)
	{
		$this->charset = defined('BX_MAIL_DEFAULT_CHARSET') && BX_MAIL_DEFAULT_CHARSET != '' ? BX_MAIL_DEFAULT_CHARSET : $charset;
		if(preg_match("'content-type:.*?charset\s*=\s*([^\r\n;]+)'is", $message_header, $res))
			$this->charset = strtolower(trim($res[1], ' "'));

		$ar_message_header_tmp = explode("\r\n", $message_header);

		$n = -1;
		$bConvertSubject = false;
		for($i = 0, $num = count($ar_message_header_tmp); $i < $num; $i++)
		{
			$line = $ar_message_header_tmp[$i];
			if(($line[0]==" " || $line[0]=="\t") && $n>=0)
			{
				$line = ltrim($line, " \t");
				$bAdd = true;
			}
			else
				$bAdd = false;

			$line = self::DecodeHeader($line, $charset, $this->charset);

			if($bAdd)
				$this->arHeaderLines[$n] = $this->arHeaderLines[$n].$line;
			else
			{
				$n++;
				$this->arHeaderLines[] = $line;
			}
		}

		$this->arHeader = Array();
		for($i = 0, $num = count($this->arHeaderLines); $i < $num; $i++)
		{
			$p = strpos($this->arHeaderLines[$i], ":");
			if($p>0)
			{
				$header_name = strtoupper(trim(substr($this->arHeaderLines[$i], 0, $p)));
				$header_value = trim(substr($this->arHeaderLines[$i], $p+1));
				$this->arHeader[$header_name] = $header_value;
			}
		}

		$full_content_type = $this->arHeader["CONTENT-TYPE"];
		if(strlen($full_content_type)<=0)
			$full_content_type = "text/plain";

		if(!($p = strpos($full_content_type, ";")))
			$p = strlen($full_content_type);

		$this->content_type = trim(substr($full_content_type, 0, $p));
		if(strpos(strtolower($this->content_type), "multipart/") === 0)
		{
			$this->bMultipart = true;
			if (!preg_match("'boundary\s*=\s*(.+?);'i", $full_content_type, $res))
				preg_match("'boundary\s*=\s*(.+)'i", $full_content_type, $res);

			$this->boundary = trim($res[1], '"');
			if($p = strpos($this->content_type, "/"))
				$this->MultipartType = substr($this->content_type, $p+1);
		}

		if($p < strlen($full_content_type))
		{
			$add = substr($full_content_type, $p+1);
			if(preg_match("'name=([^;]+)'i", $full_content_type, $res))
				$this->filename = trim($res[1], '"');
		}

		$cd = $this->arHeader["CONTENT-DISPOSITION"];
		if (strlen($cd) > 0)
		{
			if (preg_match("'filename=([^;]+)'i", $cd, $res))
			{
				$this->filename = trim($res[1], '"');
			}
			else if (preg_match("'filename\*=([^;]+)'i", $cd, $res))
			{
				list($fncharset, $fnstr) = preg_split("/'[^']*'/", trim($res[1], '"'));
				$this->filename = self::ConvertCharset(rawurldecode($fnstr), $fncharset, $charset);
			}
			else if (preg_match("'filename\*0=([^;]+)'i", $cd, $res))
			{
				$this->filename = trim($res[1], '"');

				$i = 0;
				while (preg_match("'filename\*".(++$i)."=([^;]+)'i", $cd, $res))
					$this->filename .= trim($res[1], '"');
			}
			else if (preg_match("'filename\*0\*=([^;]+)'i", $cd, $res))
			{
				$fnstr = trim($res[1], '"');

				$i = 0;
				while (preg_match("'filename\*".(++$i)."\*?=([^;]+)'i", $cd, $res))
					$fnstr .= trim($res[1], '"');

				list($fncharset, $fnstr) = preg_split("/'[^']*'/", $fnstr);
				if (!empty($fnstr))
				{
					$fnstr = rawurldecode($fnstr);
					$this->filename = $fncharset ? self::convertCharset($fnstr, $fncharset, $charset) : $fnstr;
				}
			}
		}

		if($this->arHeader["CONTENT-ID"]!='')
			$this->content_id = trim($this->arHeader["CONTENT-ID"], '"<>');

		$this->strHeader = implode("\r\n", $this->arHeaderLines);

		return true;
	}

	public static function LogMailer($text, $title = "SMTP_LOG", $type = "ERROR")
	{
		$moduleId = self::MODULE_ID;
		\CEventLog::Log($type, $title, $moduleId, $moduleId, $text, self::$siteId);
		return true;
	}
}
?>