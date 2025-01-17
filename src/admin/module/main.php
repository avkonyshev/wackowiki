<?php

if (!defined('IN_WACKO'))
{
	exit;
}

##########################################################
##	Welcome screen and site locking						##
##########################################################

$module['main'] = [
		'order'	=> 100,
		'cat'	=> 'basics',
		'status'=> true,
	];

##########################################################

function admin_main(&$engine, $module)
{
	// (un)lock website
	if (isset($_POST['action']) && $_POST['action'] == 'lock')
	{
		$engine->config->lock();

		$engine->http->redirect($engine->href());
	}
	// purge sessions
	else if (isset($_POST['action']) && $_POST['action'] == 'purge_sessions')
	{
		# $sql = "TRUNCATE " . $engine->db->table_prefix . "auth_token";
		# $engine->db->sql_query($sql);
		$engine->sess->delete_cookie(AUTH_TOKEN);
		// queries
		# $engine->config->invalidate_sql_cache();
	}
?>
	<h1><?php echo $engine->_t($module)['title']; ?></h1>
	<br>
	<p><?php echo $engine->_t('MainNote');?></p>
	<br>
	<?php
	echo $engine->form_open('lock');
	?>
	<input type="hidden" name="action" value="lock">
	<table style="max-width: 200px;" class="formation">
		<tr class="hl-setting">
			<td class="label" style="white-space: nowrap;">
				<?php echo ($engine->db->is_locked()
					? '<span class="red">' . $engine->_t('SiteClosedTip') . '</span>'
					: '<span class="green">' . $engine->_t('SiteOpenedTip') . '</span>'); ?>
			</td>
			<td class="t-center">
				<button type="submit" id="submit"><?php echo ($engine->db->is_locked() ? $engine->_t('SiteOpen') : $engine->_t('SiteClose')); ?></button>
			</td>
		</tr>
	</table>
<?php
	echo $engine->form_close();

	echo $engine->form_open('purge_sessions');
?>
		<input type="hidden" name="mode" value="main">
		<input type="hidden" name="action" value="purge_sessions">
		<table style="max-width: 200px;" class="formation">
			<tr class="hl-setting">
				<td class="label nowrap">
					<?php echo $engine->_t('PurgeSessionsTip');?>
					<br><?php #echo $engine->_t('PurgeSessionsExplain');?></td>
				<td class="t-center">
					<?php echo (isset($_POST['action']) && $_POST['action'] == 'purge_sessions' ? $engine->_t('PurgeSessionsDone') : '<button type="submit" id="submit">' . $engine->_t('PurgeSessions') . '</button>');?>
				</td>
			</tr>
		</table>
<?php
	echo $engine->form_close();
	echo '<br><br>';
	echo $engine->action('admincache');

}
