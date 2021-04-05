<?php
	global $wpdb, $current_user, $pmpro_invoice, $pmpro_msg, $pmpro_msgt;

	//if membership is a paying one, get invoice from DB - replicating some functionality from the preheader, but with an MMPU spice.
	$theselevels = pmprommpu_get_levels_from_latest_checkout(NULL, array('success', 'pending'));

	if (count($theselevels)>0 && !pmpro_areLevelsFree($theselevels)) {
		$pmpro_invoice = new MemberInvoice();
		$pmpro_invoice->getLastMemberInvoice($current_user->ID, apply_filters("pmpro_confirmation_order_status", array("success", "pending")));
	}

	if($pmpro_msg)
	{
	?>
		<div class="pmpro_message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
	<?php
	}

	$curlevels = pmpro_getMembershipLevelsForUser();
	$levelnames = array();
	$levelnums = array();
	foreach($theselevels as $thelevel) {
		$levelnames[] = $thelevel->name;
		$levelnums[] = $thelevel->id;
	}

	if(empty($theselevels)) {
		$confirmation_message = "<p>" . __('پرداخت شما ثبت شد. عضویت شما به زودی فعال خواهد شد.', 'paid-memberships-pro') . "</p>";
	} elseif(count($theselevels) == 1) {
		$confirmation_message = "<p>" . sprintf(__('با تشکر از شما برای عضویت شما برای %s. سطح عضویت %s برای شما فعال شد.', 'paid-memberships-pro'), get_bloginfo("name"), pmprommpu_join_with_and($levelnames)) . "</p>";
	} else {
		$confirmation_message = "<p>" . sprintf(__('با تشکر از شما برای عضویت شما برای %s. سطوح عضویت %s برای شما فعال شد.', 'paid-memberships-pro'), get_bloginfo("name"), pmprommpu_join_with_and($levelnames)) . "</p>";
	}

	//confirmation message for this level
	if(count($levelnums)>0) {
		$level_messages = $wpdb->get_col("SELECT confirmation FROM $wpdb->pmpro_membership_levels WHERE id IN (".implode(',',$levelnums).")");
		foreach($level_messages as $level_message) {
			$confirmation_message .= "\n" . stripslashes($level_message) . "\n";
		}
	}

	if(!empty($pmpro_invoice) && !empty($pmpro_invoice->id)) {

		$pmpro_invoice->getUser();
		$pmpro_invoice->getMembershipLevel();

		$confirmation_message .= "<p>" . sprintf(__('در زیر جزئیات مربوط به حساب عضویت شما و رسید برای فاکتور عضویت اولیه شما قابل مشاهده است. یک ایمیل خوش آمدگویی با یک کپی از فاکتور عضویت اولیه برای %s ارسال شد.', 'paid-memberships-pro'), $pmpro_invoice->user->user_email) . "</p>";

		//check instructions
		if($pmpro_invoice->gateway == "check" && !pmpro_areLevelsFree($theselevels))
			$confirmation_message .= wpautop(pmpro_getOption("instructions"));

		$confirmation_message = apply_filters("pmpro_confirmation_message", $confirmation_message, $pmpro_invoice);

		echo apply_filters("the_content", $confirmation_message);
	?>

	<h3>
		<?php printf(__('صورتحساب #%s در %s', 'pmpro'), $pmpro_invoice->code, date_i18n(get_option('date_format'), $pmpro_invoice->timestamp));?>
	</h3>
	<a class="pmpro_a-print" href="javascript:window.print()"><?php _e('چاپ', 'pmpro');?></a>
	<ul>
		<?php do_action("pmpro_invoice_bullets_top", $pmpro_invoice); ?>
		<li><strong><?php _e('Account', 'paid-memberships-pro');?>:</strong> <?php echo $current_user->display_name?> (<?php echo $current_user->user_email?>)</li>
		<?php if(count($levelnames)==1) { ?>
			<li><strong><?php _e('سطح عضویت', 'paid-memberships-pro');?>:</strong> <?php echo $current_user->membership_level->name?></li>
		<?php } else { ?>
			<li><strong><?php _e('سطوح عضویت', 'mmpu');?>:</strong> <?php echo implode(', ', $levelnames); ?></li>
		<?php } ?>
		<?php if($current_user->membership_level->enddate) { ?>
			<li><strong><?php _e('انقضای عضویت', 'paid-memberships-pro');?>:</strong> <?php echo pmpro_getLevelsExpiration($pmpro_invoice->membership_levels); ?></li>
		<?php } ?>
		<?php if($pmpro_invoice->getDiscountCode()) { ?>
			<li><strong><?php _e('کد تخفیف', 'paid-memberships-pro');?>:</strong> <?php echo $pmpro_invoice->discount_code->code?></li>
		<?php } ?>
		<?php do_action("pmpro_invoice_bullets_bottom", $pmpro_invoice); ?>
	</ul>

	<table id="pmpro_confirmation_table" class="pmpro_invoice" width="100%" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<?php if(!empty($pmpro_invoice->billing->name)) { ?>
				<th><?php _e('آدرس قبض', 'paid-memberships-pro');?></th>
				<?php } ?>
				<th><?php _e('روش پرداخت', 'paid-memberships-pro');?></th>
				<th><?php _e('سطح عضویت', 'paid-memberships-pro');?></th>
				<th><?php _e('مجموع صورتحساب', 'paid-memberships-pro');?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<?php if(!empty($pmpro_invoice->billing->name)) { ?>
				<td>
					<?php echo $pmpro_invoice->billing->name?><br />
					<?php echo $pmpro_invoice->billing->street?><br />
					<?php if($pmpro_invoice->billing->city && $pmpro_invoice->billing->state) { ?>
						<?php echo $pmpro_invoice->billing->city?>, <?php echo $pmpro_invoice->billing->state?> <?php echo $pmpro_invoice->billing->zip?> <?php echo $pmpro_invoice->billing->country?><br />
					<?php } ?>
					<?php echo formatPhone($pmpro_invoice->billing->phone)?>
				</td>
				<?php } ?>
				<td>
					<?php if($pmpro_invoice->accountnumber) { ?>
						<?php echo $pmpro_invoice->cardtype?> <?php _e('اتمام در ', 'paid-memberships-pro');?> <?php echo last4($pmpro_invoice->accountnumber)?><br />
						<small><?php _e('انقضاء', 'paid-memberships-pro');?>: <?php echo $pmpro_invoice->expirationmonth?>/<?php echo $pmpro_invoice->expirationyear?></small>
					<?php } elseif($pmpro_invoice->payment_type) { ?>
						<?php echo $pmpro_invoice->payment_type?>
					<?php } ?>
				</td>
				<td><?php echo implode('<br>', $levelnames); ?></td>
				<td><?php if($pmpro_invoice->total) echo pmpro_formatPrice($pmpro_invoice->total); else echo "---";?></td>
			</tr>
		</tbody>
	</table>
<?php
	}
	else
	{
		$confirmation_message .= "<p>" . sprintf(__('در زیر جزئیات مربوط به حساب عضویت شما و رسید برای فاکتور عضویت اولیه شما قابل مشاهده است. یک ایمیل خوش آمدگویی با یک کپی از فاکتور عضویت اولیه برای %s ارسال شد.', 'paid-memberships-pro'), $current_user->user_email) . "</p>";

		$confirmation_message = apply_filters("pmpro_confirmation_message", $confirmation_message, false);

		echo $confirmation_message;
	?>
	<ul>
		<li><strong><?php _e('حساب', 'paid-memberships-pro');?>:</strong> <?php echo $current_user->display_name?> (<?php echo $current_user->user_email?>)</li>
		<?php if(count($levelnames)<2) { ?>
			<li><strong><?php _e('سطح عضویت', 'paid-memberships-pro');?>:</strong> <?php if(count($levelnames)==1) echo $levelnames[0]; else _e("در انتظار", "pmpro");?></li>
		<?php } else { ?>
				<li><strong><?php _e('سطوح عضویت', 'mmpu'); ?>:</strong><br><span class="pmprommpu_conf_levelrow">
				<?php echo implode('</span><br><span class="pmprommpu_conf_levelrow">', $levelnames); ?>
				</span>
				</li>
		<?php } ?>
	</ul>
<?php
	}
?>
<nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-next alignright">
		<?php if(!empty($curlevels)) { ?>
			<a href="<?php echo pmpro_url("account")?>"><?php _e('مشاهده سطح عضویت حساب &rarr;', 'paid-memberships-pro');?></a>
		<?php } else {
			_e('اگر حساب شما ظرف چند دقیقه فعال نشد, لطفا با ادمین سایت تماس بگیرید.', 'paid-memberships-pro');
			} ?>
	</div>
</nav>
