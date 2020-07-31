<h3><?php _e('String Groups', 'lingotek-translation'); ?></h3>

<p class="description"><?php printf(__('Manage group translation of system, widget, and plugin-specific strings. View individual strings on the <a href="%s"><b>Strings</b></a> page.', 'lingotek-translation'), 'admin.php?page=lingotek-translation_manage&sm=strings'); ?></p>
<?php

$profile = Lingotek_Model::get_profile('string', $this->pllm->get_language($this->pllm->options['default_lang']));

if ('disabled' == $profile['profile']) {
	printf('<div class="error" style="border-left: 4px solid #ffba00;"><p>%s</p></div>', // no warning class in WP 4.0, color form .update-nag
		sprintf(__('The strings translation is disabled in %sContent Type Configuration%s.', 'lingotek-translation'),
			'<a href="' . admin_url('admin.php?page=lingotek-translation_settings&sm=content') . '">',
			'</a>'
		)
	);
}
else {
	$string_actions = $GLOBALS['wp_lingotek']->string_actions;
	$table = new Lingotek_Strings_Table($string_actions);
	$action = $table->current_action();
	if (!empty($action)) {
		ob_end_clean(); // flush the output buffer.
		$string_actions->manage_actions($action);
  }

	$data = Lingotek_Model::get_strings();
	foreach ($data as $key => $row) {
		$data[$key]['row'] = $key; // store the row number for convenience
  }
  	
	$table->prepare_items($data); ?>

	<form id="lingotek-strings" method="post" action="admin.php?page=lingotek-translation_manage&amp;noheader=true&amp;sm=string-groups"><?php
	$table->display(); ?>
	</form><?php

	foreach (Lingotek_String_actions::$actions as $action => $strings) {
		if (!empty($_GET['bulk-lingotek-' . $action])) {
			printf('<div id="lingotek-progressdialog" title="%s"><div id="lingotek-progressbar"></div></div>', $strings['progress']);
    }
	}
}
