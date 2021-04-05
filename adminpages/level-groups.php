<?php
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_membershiplevels")))
	{
		die(__("شما مجوز برای انجام این اقدام را ندارید.", 'pmprommpu'));
	}

	global $wpdb, $msg, $msgt;

	if(isset($_REQUEST['edit']))
		$edit = intval($_REQUEST['edit']);
	else
		$edit = false;

	$levelgroup = array(
		'id' => '1',
		'name' => 'پیش فرض',
		'type' => 'multiple',
	);

	if($edit)
	{
	?>

	<h2>
		<?php
			if($edit > 0)
				echo __("ویرایش گروه سطح", 'pmprommpu');
			else
				echo __("اضافه کردن گروه جدید", 'pmprommpu');
		?>
	</h2>

	<div>
		<form action="" method="post" enctype="multipart/form-data">
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('ID', 'pmprommpu');?>:</label></th>
					<td>
						<?php echo $levelgroup->id?>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label for="name"><?php _e('نام', 'pmprommpu');?>:</label></th>
					<td><input name="name" type="text" size="50" value="<?php echo esc_attr($levelgroup->name);?>"></td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label for="name"><?php _e('نوع', 'pmprommpu');?>:</label></th>
					<td>
						<select name="type" id="type">
							<option value="legacy">کاربران تنها می توانند یک سطح از این گروه را انتخاب کنند.</option>
							<option value="multiple">کاربران می توانند سطوح متعدد را از این گروه انتخاب کنند.</option>
							<!-- <option value="super">Super Levels: Users who select these levels will have all other memberships cancelled.</option> -->
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit topborder">
			<input name="save" type="submit" class="button-primary" value="<?php _e('ذخیره گروه', 'pmprommpu'); ?>">
			<input name="cancel" type="button" value="<?php _e('لغو', 'pmprommpu'); ?>" onclick="location.href='<?php echo add_query_arg( 'page', 'pmpro-membershiplevels', get_admin_url(NULL, 'admin.php') ); ?>';">
		</p>
	</form>
	</div>

	<?php
	}
