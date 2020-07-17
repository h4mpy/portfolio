<?php

class H4mpy_Npm_Package {
	const MODULE_ID = "h4mpy-npm";
	private $config = array();
	private $package = '';
	private $version = '';
	private $tarball = '';
	private $alias = false;
	private $lockFile = '';

	public function __construct($package = '') {
		if (is_file($_SERVER['DOCUMENT_ROOT'] . '/package.json')) {
			$r = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/package.json'), true);
			if (!is_null($r)) {
				$this->config = $r;
			}
		}
		$this->lockFile = $_SERVER['DOCUMENT_ROOT'] . '/npm/wp-lock.json';
		if ($package !== '') {
			$this->package = $package;
		}
	}

	/**
	 * @param string $version
	 */
	public function setVersion($version) {
		//Todo Add version check. Version must be like 3.1.1
		$this->version = $version;
	}

	/**
	 * @param string $tarball
	 */
	public function setTarball($tarball) {
		if (preg_match("/https:\/\/registry\.npmjs\.org\/(.)*\.tgz/", $tarball)) {
			$this->tarball = $tarball;
		}
	}

	/**
	 * @return array|mixed
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * @param bool|string $alias
	 */
	public function setAlias($alias) {
		//Todo Add alias check for allowed symbols
		$this->alias = $alias;
	}

	/**
	 * @return string
	 */
	public function getConfigJson() {

		$config            = $this->config;
		$config["zlib"]    = (function_exists('gzopen') || function_exists('gzopen64'));
		$installedPackages = (is_file($this->lockFile)) ? json_decode(
			file_get_contents($this->lockFile),
			true
		) : array();
		foreach ($installedPackages as $dependency => $details) {
			$path = $_SERVER['DOCUMENT_ROOT'] . '/npm/' . $dependency;
			if (is_dir($path)) {
				$files = self::getInstalledFiles($path);
				if ($files) {
					$versionParts                     = explode('@', $details["version"]);
					$config["installed"][$dependency] = array(
						"version"    => end($versionParts),
						"npmversion" => $details["version"],
						"files"      => $files
					);
					$packageName                      = explode('-v-', $dependency);
					$packageMainFiles                 = plugin_dir_path(
						                                    dirname(__FILE__)
					                                    ) . '/json/main/' . $packageName[0] . '/default.json';
					if (is_file($packageMainFiles)) {
						$config["installed"][$dependency]["main"] = json_decode(
							file_get_contents($packageMainFiles)
						);
					}
				}
			}
		}
		$themes                             = array();
		$deleted                            = array();
		$current                            = wp_get_theme();
		$themes[$current->get_stylesheet()] = array(
			'name'       => $current->get('Name'),
			'path'       => $current->get_stylesheet(),
			'screenshot' => $current->get_screenshot(),
			'version'    => $current->get('Version'),
			'author'     => $current->get('Author'),
			'authoruri'  => $current->get('AuthorURI'),
			'current'    => true
		);
		$themesOther                        = wp_get_themes();

		if (is_array($themesOther)) {
			foreach ($themesOther as $theme) {
				$themeKey = $theme->get_stylesheet();
				if (!isset($themes[$themeKey])) {
					$themes[$themeKey] = array(
						'name'       => $theme->get('Name'),
						'path'       => $theme->get_stylesheet(),
						'screenshot' => $theme->get_screenshot(),
						'version'    => $theme->get('Version'),
						'author'     => $theme->get('Author'),
						'authoruri'  => $theme->get('AuthorURI'),
						'current'    => false
					);
				}
			}
		}
		foreach ($themes as $themeKey => $theme) {
			$theme = wp_get_theme($themeKey);
			if ($theme->exists()) {
				$checkfile = $theme->get_template_directory() . '/template-lock.json';
				if (is_file($checkfile)) {
					if ($files = json_decode(file_get_contents($checkfile), true)) {
						foreach ($files as $key => $value) {
							if (!is_file($_SERVER["DOCUMENT_ROOT"] . $value['link']) && !in_array(
									$_SERVER["DOCUMENT_ROOT"] . $value['link'],
									$deleted
								)) {
								$deleted[] = $value['link'];
							}
						}

						$themes[$themeKey]["files"] = $files;
					}
				}
			}
		}
		if (count($deleted) > 0) {
			$config['deleted'] = $deleted;
		}
		$config['templates'] = $themes;

		/*		$siteTemplates = \CSiteTemplate::GetList(array("ID" => "ASC"), array(array("!TYPE" => "mail")));
				$arSiteTemplates = array();
				while($arTemplate = $siteTemplates->GetNext())
				{
					if ($arTemplate["TYPE"] !== 'mail')
					{
						if (LANG_CHARSET != 'utf-8')
						{
							$arTemplate["NAME"] = \Bitrix\Main\Text\Encoding::convertEncoding($arTemplate["NAME"], LANG_CHARSET, 'utf-8');
							$arTemplate["DESCRIPTION"] = \Bitrix\Main\Text\Encoding::convertEncoding($arTemplate["DESCRIPTION"], LANG_CHARSET, 'utf-8');
							//$arTemplate["NAME"] = mb_convert_encoding($arTemplate["NAME"], LANG_CHARSET);
							//$arTemplate["DESCRIPTION"] = mb_convert_encoding($arTemplate["DESCRIPTION"], LANG_CHARSET);
						}
						$arSiteTemplates[$arTemplate["ID"]] = array(
							"name" => $arTemplate["NAME"],
							"description" => $arTemplate["DESCRIPTION"],
							"path" => $arTemplate["PATH"],
							//"SCREENSHOT" => urlencode($arTemplate["SCREENSHOT"]),
							//"PREVIEW" => urlencode($arTemplate["PREVIEW"])
						);
						$checkfile = $_SERVER["DOCUMENT_ROOT"].$arTemplate["PATH"].'/template-lock.json';
						if (is_file($checkfile))
						{
							if ($files = json_decode(file_get_contents($checkfile), true))
							{
								$deleted = array();
								foreach ($files as $key => $value)
								{
									if (!is_file($_SERVER["DOCUMENT_ROOT"].$value['link']) && !in_array($_SERVER["DOCUMENT_ROOT"].$value['link'], $deleted))
									{
										$deleted[] = $value['link'];
									}
								}
								if (count($deleted) > 0)
								{
									$config['deleted'] = $deleted;
								}
								$arSiteTemplates[$arTemplate["ID"]]["files"] = $files;
							}
						}
						$autoMode = false;
						$headerFile = $_SERVER["DOCUMENT_ROOT"].$arTemplate["PATH"].'/header.php';
						if (is_file($headerFile) && strpos(file_get_contents($headerFile), "Npm::init()") !== false)
						{
							$autoMode = true;
						}
						$arSiteTemplates[$arTemplate["ID"]]["auto"] = $autoMode;

					}
				}
				$config['templates'] = $arSiteTemplates;*/

		return json_encode($config);
	}

	private static function getInstalledFiles($path) {
		$result = false;
		if (file_exists($path) && is_dir($path)) {
			$files = glob($path . "/*");
			if (count($files) > 0) {
				foreach ($files as $file) {
					if (is_file("$file")) {
						$result[str_replace($_SERVER['DOCUMENT_ROOT'], '', $path)][] = basename($file);
					} else if (is_dir("$file")) {
						$result[str_replace($_SERVER['DOCUMENT_ROOT'], '', $path)][] = self::getInstalledFiles(
							"$file"
						);
					}
				}
			}
		}

		return $result;
	}

	public static function getPackage($name) {
		if ($name == '') {
			return false;
		}

		$cache_time = 86400;
		$cache_key  = 'npm-' . strval($name);
		$cache_path = '';
		if ($cache = wp_cache_get($cache_key, $cache_path)) {
			return $cache;
		}

		if (function_exists('curl_init')) {
			$ch = curl_init('https://registry.npmjs.org/' . $name);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$content = curl_exec($ch);
			if (curl_errno($ch)) {
				$error_msg = curl_error($ch);
				$result    = json_encode(
					array(
						'error' => 'Failed to load',
					)
				);
			} else {
				$result = $content;
				if ($cache_time > 0 && $content !== '{"error":"Not found"}') {
					wp_cache_set($cache_key, $result, $cache_path, $cache_time);
				}
			}
			curl_close($ch);
		} else {
			$result = file_get_contents('https://registry.npmjs.org/' . $name);
			if ($result) {
				if ($result !== '{"error":"Not found"}' && $cache_time > 0) {
					wp_cache_set($cache_key, $result, $cache_path, $cache_time);
				}
			} else {
				$result = json_encode(
					array(
						'error' => 'Failed to load',
					)
				);
			}
		}

		return $result;
	}

	private static function checkDirPath($path, $bPermission = true) {
		$path = str_replace(array("\\", "//"), "/", $path);

		//remove file name
		if (substr($path, - 1) != "/") {
			$p    = strrpos($path, "/");
			$path = substr($path, 0, $p);
		}

		$path = rtrim($path, "/");

		if ($path == "") {
			//current folder always exists
			return true;
		}

		if (!file_exists($path)) {
			return mkdir($path, 0755, true);
		}

		return is_dir($path);
	}

	private static function copyDirFiles(
		$path_from,
		$path_to,
		$ReWrite = true,
		$Recursive = false,
		$bDeleteAfterCopy = false,
		$strExclude = ""
	) {
		if (strpos($path_to . "/", $path_from . "/") === 0 || realpath($path_to) === realpath($path_from)) {
			return false;
		}

		if (is_dir($path_from)) {
			self::checkDirPath($path_to . "/");
		} elseif (is_file($path_from)) {
			$p           = strrpos($path_to, "/");
			$path_to_dir = substr($path_to, 0, $p);
			self::checkDirPath($path_to_dir . "/");

			if (file_exists($path_to) && !$ReWrite) {
				return false;
			}

			@copy($path_from, $path_to);
			if (is_file($path_to)) {
				@chmod($path_to, 644);
			}

			if ($bDeleteAfterCopy) {
				@unlink($path_from);
			}

			return true;
		} else {
			return true;
		}

		if ($handle = @opendir($path_from)) {
			while (($file = readdir($handle)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}

				if (strlen($strExclude) > 0 && substr($file, 0, strlen($strExclude)) == $strExclude) {
					continue;
				}

				if (is_dir($path_from . "/" . $file) && $Recursive) {
					self::copyDirFiles(
						$path_from . "/" . $file,
						$path_to . "/" . $file,
						$ReWrite,
						$Recursive,
						$bDeleteAfterCopy,
						$strExclude
					);
					if ($bDeleteAfterCopy) {
						@rmdir($path_from . "/" . $file);
					}
				} elseif (is_file($path_from . "/" . $file)) {
					if (file_exists($path_to . "/" . $file) && !$ReWrite) {
						continue;
					}
					@copy($path_from . "/" . $file, $path_to . "/" . $file);
					@chmod($path_to . "/" . $file, 0644);

					if ($bDeleteAfterCopy) {
						@unlink($path_from . "/" . $file);
					}
				}
			}
			@closedir($handle);

			if ($bDeleteAfterCopy) {
				@rmdir($path_from);
			}

			return true;
		}

		return false;
	}

	private function loadPackage() {
		$result = false;
		if ($this->package === '' || $this->tarball === '') {
			return $result;
		}
		$fileName  = basename($this->tarball);
		$pathParts = pathinfo($this->tarball);

		$dir = plugin_dir_path(dirname(__FILE__)) . 'tmp/' . $pathParts['filename'];
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		$saveFileLocation = $dir . '/' . $fileName;
		if (function_exists('curl_init')) {
			$ch = curl_init($this->tarball);
			if ($fp = fopen($saveFileLocation, 'wb')) {
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				$result = (curl_errno($ch)) ? false : true;
				curl_close($ch);
				fclose($fp);
			}
		} else {
			$result       = false;
			$fileContents = file_get_contents($this->tarball);
			if ($fileContents) {
				if ($save = file_put_contents($saveFileLocation, $fileContents)) {
					$result = true;
				}
			}
		}
		if ($result) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/Tar.php';
			$archive = new Archive_Tar($saveFileLocation);
			if ($archive->extract($dir)) {
				$packageDir = $_SERVER["DOCUMENT_ROOT"] . "/npm/";
				$backupDir  = $packageDir . ".bak/";
				$backupDir  .= ($this->alias) ? $this->alias : $this->package;
				$packageDir .= ($this->alias) ? $this->alias : $this->package;
				if (is_dir($packageDir)) {
					if (!is_dir($backupDir)) {
						mkdir($backupDir, 0755, true);
					}
					self::copyDirFiles($packageDir, $backupDir, true, true, true);
				} else {
					mkdir($packageDir, 0755, true);
				}
				if (self::copyDirFiles($dir . "/package", $packageDir, true, true, true)) {
					self::deleteDir(plugin_dir_path(dirname(__FILE__)) . 'tmp/' . $pathParts['filename']);
					$bitrixLockConfig                                                 = (is_file(
						$this->lockFile
					)) ? json_decode(
						file_get_contents($this->lockFile),
						true
					) : array();
					$bitrixLockConfig[($this->alias) ? $this->alias : $this->package] = array(
						"resolved" => $this->tarball,
						"version"  => ($this->alias) ? 'npm:' . $this->package . '@' . $this->version : $this->version
					);
					file_put_contents(
						$this->lockFile,
						json_encode($bitrixLockConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
					);
					$jsonVersion                                                                  = explode(
						'@',
						($this->alias) ? 'npm:' . $this->package . '@' . $this->version : $this->version
					);
					$jsonVersion[]                                                                = '^' . array_pop(
							$jsonVersion
						);
					$this->config["dependencies"][($this->alias) ? $this->alias : $this->package] = implode(
						'@',
						$jsonVersion
					);
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'] . '/package.json',
						json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
					);
					chmod($_SERVER['DOCUMENT_ROOT'] . '/package.json', 0640);
				} else {
					$result = false;
				}
			} else {
				$result = false;
			}
		}

		return $result;
	}

	private static function deleteDir($path) {
		$result = true;
		if (is_file($path) || is_link($path)) {
			if (!@unlink($path)) {
				$result = false;
			}
		} elseif (is_dir($path)) {
			if ($handle = opendir($path)) {
				while (($file = readdir($handle)) !== false) {
					if ($file == "." || $file == "..") {
						continue;
					}

					self::deleteDir(self::combineDir($path, $file));
				}
				closedir($handle);
			}
			if (!@rmdir($path)) {
				$result = false;
			}
		}

		return $result;
	}

	private static function combineDir() {
		$numArgs = func_num_args();
		if ($numArgs <= 0) {
			return "";
		}

		$arParts = array();
		for ($i = 0; $i < $numArgs; $i ++) {
			$arg = func_get_arg($i);
			if (is_array($arg)) {
				if (empty($arg)) {
					continue;
				}

				foreach ($arg as $v) {
					if (!is_string($v) || $v == "") {
						continue;
					}
					$arParts[] = $v;
				}
			} elseif (is_string($arg)) {
				if ($arg == "") {
					continue;
				}

				$arParts[] = $arg;
			}
		}

		$result = "";
		foreach ($arParts as $part) {
			if ($result !== "") {
				$result .= '/';
			}
			$result .= $part;
		}

		return $result;
	}

	public function install() {
		$result = false;
		if (empty($this->package) || empty($this->version)) {
			return $result;
		}
		$npm = json_decode(self::getPackage($this->package), true);
		if (!is_null($npm) && isset($npm["versions"][$this->version]["dist"]["tarball"])) {
			$this->setTarball($npm["versions"][$this->version]["dist"]["tarball"]);
			if ($this->loadPackage()) {
				$result = true;
			}
		}

		return $result;
	}

	public function delete() {
		$result = true;
		if (empty($this->package)) {
			return false;
		}
		$baseDir    = $_SERVER["DOCUMENT_ROOT"] . "/npm/";
		$backupDir  = $baseDir . ".bak/";
		$backupDir  .= ($this->alias) ? $this->alias : $this->package;
		$package    = ($this->alias) ? $this->alias : $this->package;
		$packageDir = $baseDir . $package;
		if (!is_dir($backupDir)) {
			mkdir($backupDir, 0755, true);
		}
		self::copyDirFiles($packageDir, $backupDir, true, true, false);
		self::deleteDir($packageDir);
		if (strpos($package, '/') !== false) {
			$dir      = explode('/', $package);
			$scopeDir = $baseDir . $dir[0];
			if (is_dir($scopeDir)) {
				$emptyScope = true;
				foreach (scandir($scopeDir) as $fileCheck) {
					if (!in_array($fileCheck, array('.', '..', '.svn', '.git'))) {
						$emptyScope = false;
					}
				}
				if ($emptyScope) {
					self::deleteDir($scopeDir);
				}
			}
		}

		$bitrixLockConfig = (is_file($this->lockFile)) ? json_decode(
			file_get_contents($this->lockFile),
			true
		) : array();
		unset($bitrixLockConfig[$package]);
		file_put_contents(
			$this->lockFile,
			json_encode($bitrixLockConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);

		unset($this->config["dependencies"][$package]);
		file_put_contents(
			$_SERVER['DOCUMENT_ROOT'] . '/package.json',
			json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);

		return $result;
	}

	public static function saveInstalled($template, $files) {
		$files  = stripslashes($files);
		$result = true;
		$theme  = wp_get_theme(strval($template));
		if ($theme->exists()) {
			$path     = $theme->get_template_directory();
			$pathFile = $path . '/template-lock.json';
			if ($files == '[]') {
				if (is_file($pathFile)) {
					unlink($pathFile);
				}
			} elseif ($setFiles = json_decode($files)) {
				file_put_contents($pathFile, json_encode($setFiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			} elseif ($setFiles = json_encode($files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) {
				file_put_contents($pathFile, $setFiles);
			} else {
				$result = false;
			}

		} else {
			$result = false;
		}

		return $result;
	}

}