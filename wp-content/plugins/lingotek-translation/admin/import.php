<div class="wrap">
    <h2><?php _e('Import', 'wp-lingotek'); ?></h2>
    <p class="description"><?php printf(__('Import your posts from another wordpress blog through Lingotek', 'wp-lingotek'), 'admin.php?page=wp-lingotek_import'); ?></p>


    <?php
    $menu_items = array(
      'content' => __('Content', 'wp-lingotek'),
      'settings' => __('Settings', 'wp-lingotek')
    );
    ?>

    <h3 class="nav-tab-wrapper">
      <?php
      $menu_item_index = 0;
      foreach ($menu_items as $menu_item_key => $menu_item_label) {
        $use_as_default = ($menu_item_index === 0 && !isset($_GET['sm'])) ? TRUE : FALSE;
        $alias = NULL;
        // custom sub sub-menus
        if(isset($_GET['sm']) && $_GET['sm'] == "edit-profile") {
          $alias = "profiles";
        }
        ?>

        <a class="nav-tab <?php if ($use_as_default || (isset($_GET['sm']) && $_GET['sm'] == $menu_item_key) || $alias == $menu_item_key): ?> nav-tab-active<?php endif; ?>"
           href="admin.php?page=<?php echo $_GET['page']; ?>&amp;sm=<?php echo $menu_item_key; ?>"><?php echo $menu_item_label; ?></a>
           <?php
           $menu_item_index++;
         }
         ?>
    </h3>

    <?php
    settings_errors();
    $submenu = isset($_GET['sm']) ? $_GET['sm'] : 'content';
    $dir = dirname(__FILE__) . '/import/';
    $filename = $dir . 'view-' . $submenu . ".php";
    if (file_exists($filename))
      include $filename;
    else
      echo "TO-DO: create <i>" . 'import/view-' . $submenu . ".php</i>";
    ?>

</div>
