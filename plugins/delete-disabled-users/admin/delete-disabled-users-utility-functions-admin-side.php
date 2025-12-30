<?php

function delete_disabled_users_handle_main_page() {
	/* Display the logs table */
    //Create an instance of our package class...
    $disabled_users_ListTable = new disabled_users_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $disabled_users_ListTable->prepare_items();
    ?>

	<h2><?php _e( 'Disabled Users'); ?></h2>

    <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
    <p><?php _e( 'This page lists all tracked users whose status is disabled and they didn’t logged-in for last 6 months.' ); ?></p>
    </div>

    <!-- <div id="poststuff">
        <div id="post-body">
            <div class="postbox">
                <h3 class="hndle"><label for="title">Delete All Tracked Disabled Users that didn’t logged-in for last 6 months</label></h3>
                <div class="inside">
                    <form method="post" action="" onSubmit="return confirm('Are you sure you want to reset all the log entries?');" >
                    <div class="submit">
                        <input type="submit" class="button" name="delete_disabled_users_entries" value="Reset Log Entries" />
                    </div>
                    </form>
                </div>
            </div>

        </div>
    </div> -->
    <form id="disableduser-filter" method="post">
    	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ); ?>" />
    	<!-- Now we can render the completed list table -->
    	<?php $disabled_users_ListTable->display() ?>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
	        $('.fade').click(function () {
	            $(this).fadeOut('slow');
	        });
        });
    </script>
    <?php

}