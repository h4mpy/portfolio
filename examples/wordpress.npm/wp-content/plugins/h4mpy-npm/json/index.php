<?php
include $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
	? "https://"
	: "http://";

$origin = (!empty($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === "http://localhost:8080")
	? "http://localhost:8080"
	: $protocol . $_SERVER['HTTP_HOST'];

//$origin = $protocol.$_SERVER['HTTP_HOST'];

header("Access-Control-Allow-Origin: " . $origin);
header("Access-Control-Allow-Credentials: true");
header("Content-type: application/json; charset=utf-8");

if (current_user_can('edit_themes')) {
	require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-h4mpy-npm-package.php';

	if (isset($_GET['package']) && $_GET['package'] != '') {
		echo H4mpy_Npm_Package::getPackage($_GET['package']);
	}
	if (isset($_GET['config'])) {
		$npm = new H4mpy_Npm_Package();
		echo $npm->getConfigJson();
	}
	if (isset($_POST['install']) && $_POST['install'] != '' && isset($_POST['version']) && $_POST['version'] != '') {
		$npm = new H4mpy_Npm_Package(strval($_POST['install']));
		$npm->setVersion($_POST['version']);
		if (isset($_POST['alias']) && $_POST['alias'] !== false) {
			$npm->setAlias(strval($_POST['alias']));
		}
		$result = $npm->install();
		if ($result) {
			echo $npm->getConfigJson();
		} else {
			echo json_encode(
				array(
					'error' => "Cant install package",
				),
				JSON_UNESCAPED_UNICODE
			);
		}
	}
	if (isset($_POST['delete']) && $_POST['delete'] != '') {
		$npm = new H4mpy_Npm_Package(strval($_POST['delete']));

		if (isset($_POST['alias']) && $_POST['alias'] !== false) {
			$npm->setAlias(strval($_POST['alias']));
		}
		$result = $npm->delete();
		if ($result) {
			echo $npm->getConfigJson();
		} else {
			echo json_encode(
				array(
					'error' => 'Package cannot be deleted',
				),
				JSON_UNESCAPED_UNICODE
			);
		}
	}
	if (isset($_POST['savetemplate']) && $_POST['savetemplate'] != '' && isset($_POST['files']) && $_POST['files'] != '') {
		$result = H4mpy_Npm_Package::saveInstalled($_POST['savetemplate'], $_POST['files']);
		if ($result) {
			echo json_encode(
				array(
					'success' => true
				),
				JSON_UNESCAPED_UNICODE
			);
		} else {
			echo json_encode(
				array(
					'error' => "Error saving settings",
				),
				JSON_UNESCAPED_UNICODE
			);
		}
	}
} else {
	echo json_encode(
		array(
			'wpaccess' => 'D',
		)
	);
}
