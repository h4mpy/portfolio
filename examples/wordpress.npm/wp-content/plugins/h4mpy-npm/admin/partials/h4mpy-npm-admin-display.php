<div class="wrap">
	<h1><?php _e('NPM client (Quick installation and updating of JS packages)', 'h4mpy-npm'); ?></h1>
	<noscript>
		<strong><?php _e(
				"We're sorry but plugin doesn't work properly without JavaScript enabled. Please enable it to continue.",
				'h4mpy-npm'
			); ?></strong>
	</noscript>
	<script>
		<?php
		$timeAgoLocales = array(
			'af',
			'arDZ',
			'arMA',
			'arSA',
			'az',
			'be',
			'bg',
			'bn',
			'ca',
			'cs',
			'cy',
			'da',
			'de',
			'el',
			'enAU',
			'enCA',
			'enGB',
			'enIN',
			'enUS',
			'eo',
			'es',
			'et',
			'eu',
			'faIR',
			'fi',
			'fr',
			'frCA',
			'gl',
			'gu',
			'he',
			'hi',
			'hr',
			'hu',
			'hy',
			'id',
			'is',
			'it',
			'ja',
			'ka',
			'kk',
			'kn',
			'ko',
			'lt',
			'lv',
			'mk',
			'ms',
			'mt',
			'nb',
			'nl',
			'nn',
			'pl',
			'pt',
			'ptBR',
			'ro',
			'ru',
			'sk',
			'sl',
			'sr',
			'srLatn',
			'sv',
			'ta',
			'te',
			'th',
			'tr',
			'ug',
			'uk',
			'uz',
			'vi',
			'zhCN',
			'zhTW'
		);
		$currentLocale = explode('_', get_locale());
		$currentLocaleFull = implode('', $currentLocale);
		$selectedLocale = (in_array($currentLocaleFull, $timeAgoLocales))
			? $currentLocaleFull
			: (in_array($currentLocale[0], $timeAgoLocales))
				? $currentLocale[0]
				: 'en';
		?>
		window.npmLocalization = {
			tabinstall: "<?php _e('Package installation', 'h4mpy-npm'); ?>",
			tabinclude: "<?php _e('Theme settings', 'h4mpy-npm'); ?>",
			edit: "<?php _e('Edit', 'h4mpy-npm'); ?>",
			middot: "&#8226;",
			button: "<?php _e('Execute', 'h4mpy-npm'); ?>",
			search: "<?php _e('Search, command line', 'h4mpy-npm'); ?>",
			install: "<?php _e('Install', 'h4mpy-npm'); ?>",
			installed: "<?php _e('Installed', 'h4mpy-npm'); ?>",
			delete: "<?php _e('Delete', 'h4mpy-npm'); ?>",
			deletefromjson: "<?php _e('Delete from package.json', 'h4mpy-npm'); ?>",
			confirm: "<?php _e('Are you sure?', 'h4mpy-npm'); ?>",
			yes: "<?php _e('Yes', 'h4mpy-npm'); ?>",
			no: "<?php _e('No', 'h4mpy-npm'); ?>",
			published: "<?php _ex('Published', 'Without author name', 'h4mpy-npm'); ?>",
			publishedlower: "<?php _ex('published', 'With author name', 'h4mpy-npm'); ?>",
			locale: "<?php echo $selectedLocale; ?>", //en or ru
			readme: "<?php _e('Readme', 'h4mpy-npm'); ?>",
			loading: "<?php _e('Loading', 'h4mpy-npm'); ?>",
			notfound: "<?php _e('Package not found', 'h4mpy-npm'); ?>",
			notfoundtemplate: "<?php _e('Theme not found', 'h4mpy-npm'); ?>",
			loadingerror: "<?php _e('Package loading error', 'h4mpy-npm'); ?>",
			loadingpackageerror: "<?php _e('Failed to load package details', 'h4mpy-npm'); ?>",
			nodefaultfileset: "<?php _e(
				"This package doesn't have a default file set. You can choose files to include manually.",
				'h4mpy-npm'
			); ?>",
			copyurl: "<?php _e('Copy file path', 'h4mpy-npm'); ?>",
			copyphp: "<?php _e('Copy PHP code', 'h4mpy-npm'); ?>",
			copyhtml: "<?php _e('Copy HTML code', 'h4mpy-npm'); ?>",
			opennewwindow: "<?php _e('Open in a new window', 'h4mpy-npm'); ?>",
			error: "<?php _e('Error', 'h4mpy-npm'); ?>",
			backlink: "<?php _e('Back to package list', 'h4mpy-npm'); ?>",
			backlinksearch: "<?php _e('Back to search results', 'h4mpy-npm'); ?>",
			backlinktemplate: "<?php _e('Back to theme list', 'h4mpy-npm'); ?>",
			userlibrary: "<?php _e('User library', 'h4mpy-npm'); ?>",
			selectedfiles: "<?php _e('Selected files', 'h4mpy-npm'); ?>",
			selectedfilesnone: "<?php _e(
				'No files selected. Select the files you want to use in your theme using the switches on the left.',
				'h4mpy-npm'
			); ?>",
			refresh: "<?php _e('Refresh', 'h4mpy-npm'); ?>",
			reload: "<?php _e('Reload', 'h4mpy-npm'); ?>",
			version: "<?php _e('Version', 'h4mpy-npm'); ?>",
			willinstall: "<?php _e('Version will be installed', 'h4mpy-npm'); ?>",
			cantinstall: "<?php _e('Version cannot be installed', 'h4mpy-npm'); ?>",
			requestedversion: "<?php _e('Requested version', 'h4mpy-npm'); ?>",
			versioninstalled: "<?php _e('Installed version', 'h4mpy-npm'); ?>",
			errorduringinstall: "<?php _e('Error during installation', 'h4mpy-npm'); ?>",
			license: "<?php _e('License', 'h4mpy-npm'); ?>",
			homepage: "<?php _e('Homepage', 'h4mpy-npm'); ?>",
			more: "<?php _e('More', 'h4mpy-npm'); ?>",
			installing: "<?php _e('Installing', 'h4mpy-npm'); ?>",
			package: "<?php _e('package', 'h4mpy-npm'); ?>",
			npmlink: "<?php _e('NPM link', 'h4mpy-npm'); ?>",
			repository: "<?php _e('Repository', 'h4mpy-npm'); ?>",
			lastupdate: "<?php _e('Last update', 'h4mpy-npm'); ?>",
			nodescription: "<?php _e('No description provided', 'h4mpy-npm'); ?>",
			keywords: "<?php _e('Keywords', 'h4mpy-npm'); ?>",
			versions: ["<?php _e('version', 'h4mpy-npm'); ?>", "<?php _ex(
				'versions',
				'Several',
				'h4mpy-npm'
			); ?>", "<?php _ex('versions', 'Many', 'h4mpy-npm'); ?>"],
			packages: ["<?php _e('package', 'h4mpy-npm'); ?>", "<?php _ex(
				'packages',
				'Several',
				'h4mpy-npm'
			); ?>", "<?php _ex('packages', 'Many', 'h4mpy-npm'); ?>"],
			packagesfound: "<?php _e('found', 'h4mpy-npm'); ?>",
			packagenotetitle: "<?php _e('Tip', 'h4mpy-npm'); ?>",
			packagenote: "<?php _e(
				"Click on a version number to view a previous version's package page",
				'h4mpy-npm'
			); ?>",
			currenttags: "<?php _e('Current Tags', 'h4mpy-npm'); ?>",
			versionhistory: "<?php _e('Version History', 'h4mpy-npm'); ?>",
			showdeprecated: "<?php _e('show deprecated versions', 'h4mpy-npm'); ?>",
			deprecatedtitle: "<?php _e('This package has been deprecated', 'h4mpy-npm'); ?>",
			deprecatedauthor: "<?php _e('Author message', 'h4mpy-npm'); ?>",
			dependencies: "<?php _e('Dependencies', 'h4mpy-npm'); ?>",
			error404: "<?php _e('Page not found', 'h4mpy-npm'); ?>",
			emptyquery: "<?php _e('Please enter a search query', 'h4mpy-npm'); ?>",
			latestversion: "<?php _e('Latest version', 'h4mpy-npm'); ?>",
			phrases: {
				deprecated: "<?php _e('deprecated', 'h4mpy-npm'); ?>",
				unstable: "<?php _e('unstable', 'h4mpy-npm'); ?>",
				insecure: "<?php _e('insecure', 'h4mpy-npm'); ?>",
				exact: "<?php _e('exact match', 'h4mpy-npm'); ?>"
			},
			npm: "NPM",
			maintenance: "<?php _e('Maintenance', 'h4mpy-npm'); ?>",
			popularity: "<?php _e('Popularity', 'h4mpy-npm'); ?>",
			quality: "<?php _e('Quality', 'h4mpy-npm'); ?>",
			sortpackagestitle: "<?php _e('Sort Packages', 'h4mpy-npm'); ?>",
			sortoptimal: "<?php _e('Optimal', 'h4mpy-npm'); ?>",
			sortpopularity: "<?php _e('Popularity', 'h4mpy-npm'); ?>",
			sortmaintenance: "<?php _e('Maintenance', 'h4mpy-npm'); ?>",
			sortquality: "<?php _e('Quality', 'h4mpy-npm'); ?>",
			noreadrights: "<?php _e('Wordpress authorization expired. Please refresh the page.', 'h4mpy-npm'); ?>",
			updatepre: "<?php _e('Version', 'h4mpy-npm'); ?>",
			updatepost: "<?php _e('available', 'h4mpy-npm'); ?>",
			updatebutton: "<?php _e('Update to', 'h4mpy-npm'); ?>",
			majorupdate: "<p><b><?php _e('Attention!', 'h4mpy-npm'); ?></b> <?php _e(
				'A major version update may disrupt your libraries. Such updates often contain new methods, functions, and settings. The old ones can be rewritten or deleted. This may cause errors in your scripts and dependent libraries.',
				'h4mpy-npm'
			); ?> </p><p><?php _e('Are you sure you want to install the update?', 'h4mpy-npm'); ?></p>",
			templatesempty: "<?php _e('Theme list is empty', 'h4mpy-npm'); ?>",
			includefiles: "<?php _e('Save and include files', 'h4mpy-npm'); ?>",
			includefilesmanual: "<?php _e('Code for manual install', 'h4mpy-npm'); ?>",
			includefilessaved: "<?php _e('All changes are saved', 'h4mpy-npm'); ?>",
			savingerror: "<?php _e('Error while saving settings. Try again later.', 'h4mpy-npm'); ?>",
			save: "<?php _e('Save', 'h4mpy-npm'); ?>",
			filedeleted: "<?php _e('Package was uninstalled', 'h4mpy-npm'); ?>",
			installinstruction1: "<?php _e(
				'Load the javascript package by entering its name in the search bar, or using the',
				'h4mpy-npm'
			); ?> <br><code>npm install &lt;library name&gt;",
			installinstruction2: "<?php _e(
				'Include the necessary package files in the site template',
				'h4mpy-npm'
			); ?>",
			installinstruction3: "<?php _e(
				'The package is successfully included! When a new version appears, you can update the version on the site in 1 click.',
				'h4mpy-npm'
			); ?>",
			npmwrongcommand: "<?php _e('Wrong npm command format', 'h4mpy-npm'); ?>",
			npmauthexpired: "<?php _e('Wordpress authorization expired. Please refresh the page.', 'h4mpy-npm'); ?>",
			npmserverunavailable: "<?php _e('Server is unavailable now', 'h4mpy-npm'); ?>",
			npmcommandline: "<p><b><?php _e('NPM command not recognized.', 'h4mpy-npm'); ?></b></p><p><?php _e(
				'The module currently supports these commands',
				'h4mpy-npm'
			); ?>:</p><p><b><?php _e(
				'Installation',
				'h4mpy-npm'
			); ?></b></p><p><code>npm install &lt;package-name&gt;</code> <?php _e(
				'or',
				'h4mpy-npm'
			); ?> <code>npm i &lt;package-name&gt;</code> &mdash; <?php
				/* translators: 1: Package name, 2: folder. */
				printf(
					__('install %1$s to %2$s folder', 'h4mpy-npm'),
					'<code>&lt;package-name&gt;</code>',
					'<code>/npm/</code>'
				);
				?> </p><p><code>npm install &lt;package-name&gt;@&lt;version&gt;</code> &mdash; <?php
				/* translators: Package name */
				printf(__('install specific %s version', 'h4mpy-npm'), '<code>&lt;package-name&gt;</code>');
				?>. <code>&lt;version&gt;</code> &mdash; <?php
				/* translators: Link to semver description */
				printf(
					addslashes(
						wp_kses(
							__(
								'any version according to <a href="%s" target="_blank">semantic versioning rules</a>',
								'h4mpy-npm'
							),
							array('a' => array('href' => true, 'target' => true))
						)
					),
					'https://docs.npmjs.com/about-semantic-versioning'
				)
				?></p><p><?php _e('For example', 'h4mpy-npm'); ?>:</p><p><code>npm i bootstrap</code> &mdash; <?php
				/* translators: Package name */
				printf(__('install %s latest version', 'h4mpy-npm'), 'bootstrap');
				?></p><p><code>npm i bootstrap@3</code> &mdash; <?php
				/* translators: 1: Package name, 2: Version range */
				printf(
					__('install %1$s latest version from range %2$s with only minor updates possible', 'h4mpy-npm'),
					'bootstrap',
					'<code>3.x.x</code>'
				);
				?></p>",
			npmnotfound: "<?php _e('Package not found', 'h4mpy-npm'); ?>",
			npmcantinstall: "<?php _e('This version cannot be installed', 'h4mpy-npm'); ?>",
			npmalreadyinstalled: "<?php _e('This package already installed', 'h4mpy-npm'); ?>",
			npmnotfoundpackage: "<?php
				/* translators: 1: Config file name (package.json) */
				printf(__('Packages for install not found in %s', 'h4mpy-npm'), 'package.json');
				?>",
			npmallinstalled: "<?php _e('All packages already installed', 'h4mpy-npm'); ?>",
			emptypackagelist: "<?php _e(
				'Packages are not yet installed. First install the necessary packages.',
				'h4mpy-npm'
			); ?>",
			arrright: "&rarr;",
			zliberror: "<?php _e(
				'The zlib extension for php is required for the module to work correctly',
				'h4mpy-npm'
			); ?>",
			currenttheme: "<?php _e('Current theme', 'h4mpy-npm'); ?>",
			author: "<?php _e('Author', 'h4mpy-npm'); ?>",
			customize: "<?php _e('Customize', 'h4mpy-npm'); ?>",
			customizingtheme: "<?php _e('Customizing theme', 'h4mpy-npm'); ?>",
			notice: "<?php _e('Notice', 'h4mpy-npm'); ?>",
			jquerynotice: "<?php _e(
				'The jQuery library is already built into the Wordpress core. To avoid conflicts, make sure that your theme does not include the built-in version of jQuery and does not include scripts that use it.',
				'h4mpy-npm'
			); ?>"
		};
		window.jsonlink =
			"<?echo plugins_url()?>/h4mpy-npm/json/";
	</script>
	<div id="app"></div>
	<script src="<? echo plugins_url() ?>/h4mpy-npm/admin/npm/dist/js/chunk-vendors.js"></script>
	<script src="<? echo plugins_url() ?>/h4mpy-npm/admin/npm/dist/js/app.js"></script>
</div>

