<!-- Redirect Access Token -->
<script>
	var hash = window.location.hash;
	if (hash.length && hash.indexOf("access_token") !== -1) {
	var url_with_access_token = window.location.origin + window.location.pathname + window.location.search + '&' + hash.substr(1);
	window.location.href = url_with_access_token;
	}
	else if (window.location.search.indexOf("connect") != -1) {
	window.location.href = "<?php echo esc_url_raw( $connect_url ) ?>";
	}
</script>
<!-- Connect Your Account Button -->
<div class="wrap">
	<h2><?php esc_html_e( 'Connect Your Account', 'lingotek-translation' ) ?></h2>
	<div>
	<p class="description">
	<?php esc_html_e( 'Get started by clicking the button below to connect your Lingotek account to this Wordpress installation.', 'lingotek-translation' ) ?>
	</p>
	<hr/>
	<p>
	<a class="button button-large button-hero" href="<?php echo esc_url_raw( $connect_account_cloak_url_new ) ?>">
	  <img src="<?php echo esc_url_raw( LINGOTEK_URL ); ?>/img/lingotek-icon.png" style="padding: 0 4px 2px 0;" align="absmiddle"/> <?php esc_html_e( 'Connect New Account', 'lingotek-translation' ) ?>
	</a>
	</p>
	<hr/>
	<p class="description">
	<?php
	  $allowed_html = array(
		'a' => array(
		  'href' => array(),
		),
		);

	  echo sprintf( wp_kses( __( 'Do you already have a Lingotek account? <a href="%s">Connect Lingotek Account</a>', 'lingotek-translation' ), $allowed_html ), esc_attr( $connect_account_cloak_url_prod ) )
	?>
	</p>
	</div>
</div>
