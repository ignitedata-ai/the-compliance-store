<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.presstigers.com/
 * @since      1.0.0
 *
 * @package    Tcs_Search
 * @subpackage Tcs_Search/admin/partials
 */
?>
<?php
function tcs_handle_search_main_tab_page(){
	if ($_POST["tcs_search"]) {
		update_option( 'tcs_search', $_POST["tcs_search"]);
	}
	$tcs_search = get_option('tcs_search');
    ?>
    <h2><?php _e( 'Extended Search', 'tcs-search' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'This page allows yout to enable/disable Extended Search.', 'tcs-search' ); ?></p>
        </div>

        <div id="poststuff"><div id="post-body">

            <!-- Log reset button -->
            <div class="postbox">
            <h3 class="hndle"><label for="title"><?php _e( 'Basic Settings', 'tcs-search' ); ?></label></h3>
            <div class="inside">
                <form method="post" action="" >
                	<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">Extended Search </th>
								<td>
									<fieldset>
										<p>
											<input type="radio"  name="tcs_search" value="enable" <?php echo ($tcs_search == 'enable') ?  "checked" : "" ;  ?>> 
											<label for="enable">Enable</label>
											<br>
											<input type="radio" value="disable" name="tcs_search" <?php echo ($tcs_search == 'disable') ?  "checked" : "" ;  ?>>
											<label for="disable">Disable</label>
										</p>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
                <div class="submit">
                    <input type="submit" class="button" name="tcs_search_save" value="<?php _e( 'Save Settings', 'tcs-search' ); ?>" />
                </div>
                </form>
            </div>
            </div>

        </div></div><!-- end of .poststuff and .post-body -->
    <?php
}