<?php

if (!defined('IN_WACKO'))
{
	exit;
}

########################################################
##   Bad Behavior                                     ##
########################################################

$module['badbehavior'] = array(
		'order'	=> 30,
		'cat'	=> 'Extention',
		'status'=> (RECOVERY_MODE ? false : true),
		'mode'	=> 'badbehavior',
		'name'	=> 'Bad Behavior',
		'title'	=> 'Bad Behavior',
		'vars'	=> array(&$tables, &$directories),
	);

########################################################
function admin_badbehavior(&$engine, &$module)
{
	// import passed variables and objects
	$tables			= & $module['vars'][0];
	$directories	= & $module['vars'][1];

require_once('lib/bad_behavior/bad-behavior/responses.inc.php');

function bb2_httpbl_lookup($ip)
{
	// NB: Many of these are defunct
	$engines = array(
			1 => 'AltaVista',
			2 => 'Teoma/Ask Crawler',
			3 => 'Baidu Spide',
			4 => 'Excite',
			5 => 'Googlebot',
			6 => 'Looksmart',
			7 => 'Lycos',
			8 => 'msnbot',
			9 => 'Yahoo! Slurp',
			10 => 'Twiceler',
			11 => 'Infoseek',
			12 => 'Minor Search Engine',
	);

	$settings		= bb2_read_settings();
	$httpbl_key		= $settings['httpbl_key'];

	if (!$httpbl_key) return false;

	$r = $_SESSION['httpbl'][$ip];
	$d = '';

	if (!$r)
	{	// Lookup
		$find		= implode('.', array_reverse(explode('.', $ip)));
		$result		= gethostbynamel("${httpbl_key}.${find}.dnsbl.httpbl.org.");

		if (!empty($result))
		{
			$r = $result[0];
			$_SESSION['httpbl'][$ip] = $r;
		}
	}

	if ($r)
	{	// Interpret
		$ip = explode('.', $r);

		if ($ip[0] == 127)
		{
			if ($ip[3] == 0)
			{
				if ($engines[$ip[2]])
				{
					$d .= $engines[$ip[2]];
				}
				else
				{
					$d .= "Search engine ${ip[2]}<br/>\n";
				}
			}

			if ($ip[3] & 1)
			{
				$d .= "Suspicious<br/>\n";
			}

			if ($ip[3] & 2)
			{
				$d .= "Harvester<br/>\n";
			}

			if ($ip[3] & 4)
			{
				$d .= "Comment Spammer<br/>\n";
			}

			if ($ip[3] & 7)
			{
				$d .= "Threat level ${ip[2]}<br/>\n";
			}

			if ($ip[3] > 0)
			{
				$d .= "Age ${ip[1]} days<br/>\n";
			}
		}
	}

	return $d;
}

function bb2_summary(&$engine) {

	$request_uri	= $_SERVER['REQUEST_URI'];
	$bb_table		= $engine->config['table_prefix'] . 'bad_behavior';

	if (!$request_uri)
	{
		$request_uri = $_SERVER['SCRIPT_NAME'];	# IIS
	}

	$settings		= bb2_read_settings();

	$where			= '';

	$key_pagination				= isset($_GET['status_key'])		? $_GET['status_key']	: '';
	$blocked_pagination			= isset($_GET['blocked'])			? $_GET['blocked']		: '';
	$permitted_pagination		= isset($_GET['permitted'])			? $_GET['permitted']	: '';
	$ip_pagination				= isset($_GET['ip'])				? $_GET['ip']			: '';
	$user_agent_pagination		= isset($_GET['user_agent'])		? $_GET['user_agent']	: '';
	$request_method_pagination	= isset($_GET['request_method'])	? $_GET['request_method']	: '';
	#$level_pagination			= isset($_GET['level'])		? $_GET['level']		: (isset($_POST['level'])		? $_POST['level']		: '');
	#$level_mod_pagination		= isset($_GET['level_mod'])	? $_GET['level_mod']	: (isset($_POST['level_mod'])	? $_POST['level_mod']	: '');
	?>
	<h2>Summary</h2>

	<?php
	echo $engine->form_open('bb2_manage', '', 'post', true, '', 'setting=bb2_manage');
	?>
	<p class="right">See also: Summary | <a href="<?php echo "?mode=badbehavior&amp;setting=bb2_manage"; ?>">Log</a> | <a href="<?php echo "?mode=badbehavior&amp;setting=bb2_options" ?>">Settings</a> | <a href="<?php echo "?mode=badbehavior&amp;setting=bb2_whitelist" ?>">Whitelist</a></p>


	<div class="alignleft">
<?php bb2_insert_stats(true); ?>
	</div>
<?php
	// select arguments
	$arguments = array(
			'status_key',
			'request_method',
			#'ip',
			#'user_agent',
			'request_uri');

	foreach ($arguments as $argument)
	{
		// Query the DB based on variables selected
		$results		= $engine->load_all(
			"SELECT {$argument} as group_type, COUNT(log_id) AS n ".
			"FROM {$engine->config['table_prefix']}bad_behavior GROUP BY {$argument} ".
			"ORDER BY n DESC ".
			"LIMIT 10", true);

	// Display rows to the user

	?>
<table class="formation">
<caption><?php echo $argument; ?></caption>
	<thead>
		<tr>
			<th scope="col">Hits</th>
			<th scope="col"><?php echo $argument; ?></th>
		</tr>
	</thead>
	<tbody>
<?php
		// all ip related host names

		if ($results)
		{
			foreach ($results as $result)
			{
				echo '<tr id="request-' . '' . '"  style="vertical-align:top;" class="lined">'."\n";
				echo '<td class="label">'.$result['n']."</td>\n";
				#echo "<td>" . str_replace("\n", "<br/>\n", htmlspecialchars($result['request_entity'], ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET)) . "</td>\n";

				if ($argument == 'status_key')
				{
					$status_key	= bb2_get_response($result['group_type']);
					$link		= '<a href="' . '?mode=badbehavior&amp;setting=bb2_manage&amp;status_key='.$result['group_type'] . '" title="' .'['.$status_key['response'].'] '.$status_key['explanation']. '">' . $status_key['log'] . "</a>\n";
				}
				else
				{
					$link		= '<a href="' . '?mode=badbehavior&amp;setting=bb2_manage&amp;'.$argument.'='.$result['group_type'] . '" title="' .'['.''.'] '.''. '">' . $result['group_type'] . "</a>\n";
				}

				echo '<td>'.
						$link;
				echo "</td>\n";
				echo "</tr>\n";
			}
		}
?>
	</tbody>
</table>
<?php
	}
}

function bb2_manage(&$engine)
{

	$request_uri	= $_SERVER['REQUEST_URI'];
	$bb_table		= $engine->config['table_prefix'] . 'bad_behavior';

	if (!$request_uri)
	{
		$request_uri = $_SERVER['SCRIPT_NAME'];	# IIS
	}

	$settings		= bb2_read_settings();

	$where			= '';

	// entries to display
	$limit = 100;

	// Get query variables desired by the user with input validation
	if (isset($_GET['status_key']) && $_GET['status_key'])			$where .= "AND `status_key`			= '" . quote($engine->dblink, $_GET['status_key']) . "' ";
	if (isset($_GET['blocked']) && $_GET['blocked'])				$where .= "AND `status_key` 		!= '00000000' ";
	else if (isset($_GET['permitted']) && $_GET['permitted'])		$where .= "AND `status_key`			= '00000000' ";

	if (isset($_GET['ip']) && $_GET['ip'])							$where .= "AND `ip` 				= '" . quote($engine->dblink, $_GET['ip']) . "' ";
	if (isset($_GET['user_agent']) && $_GET['user_agent'])			$where .= "AND `user_agent_hash`	= '" . quote($engine->dblink, $_GET['user_agent']) . "' ";
	if (isset($_GET['request_method']) && $_GET['request_method'])	$where .= "AND `request_method`		= '" . quote($engine->dblink, $_GET['request_method']) . "' ";
	if (isset($_GET['request_uri']) && $_GET['request_uri'])		$where .= "AND `request_uri`		= '" . quote($engine->dblink, $_GET['request_uri']) . "' ";

	// collecting data
	$count = $engine->load_single(
		"SELECT COUNT(log_id) AS n ".
		"FROM {$engine->config['table_prefix']}bad_behavior l ".
		"WHERE 1=1 " .( $where ? $where : '' ));

	$key_pagination				= isset($_GET['status_key'])		? $_GET['status_key']	: '';
	$blocked_pagination			= isset($_GET['blocked'])			? $_GET['blocked']		: '';
	$permitted_pagination		= isset($_GET['permitted'])			? $_GET['permitted']	: '';
	$ip_pagination				= isset($_GET['ip'])				? $_GET['ip']			: '';
	$user_agent_pagination		= isset($_GET['user_agent'])		? $_GET['user_agent']	: '';
	$request_method_pagination	= isset($_GET['request_method'])	? $_GET['request_method']	: '';
	#$level_pagination			= isset($_GET['level'])				? $_GET['level']		: (isset($_POST['level'])		? $_POST['level']		: '');
	#$level_mod_pagination		= isset($_GET['level_mod'])			? $_GET['level_mod']	: (isset($_POST['level_mod'])	? $_POST['level_mod']	: '');

	$pagination				= $engine->pagination($count['n'], $limit, 'p', 'mode=badbehavior&amp;setting=bb2_manage'.
								(!empty($blocked_pagination)			? '&amp;blocked='.htmlspecialchars($blocked_pagination, ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET) : '').
								(!empty($permitted_pagination)			? '&amp;permitted='.htmlspecialchars($permitted_pagination, ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET) : '').
								(!empty($key_pagination)				? '&amp;status_key='.htmlspecialchars($key_pagination, ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET) : '').
								(!empty($ip_pagination)					? '&amp;ip='.htmlspecialchars($ip_pagination, ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET) : '').
								(!empty($request_method_pagination)		? '&amp;request_method='.htmlspecialchars($request_method_pagination, ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET) : '').
								(!empty($user_agent_pagination)			? '&amp;user_agent='.htmlspecialchars($user_agent_pagination, ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET) : ''), '', 'admin.php');

	// Query the DB based on variables selected

	$totalcount		= $engine->load_single(
		"SELECT COUNT(log_id) AS n ".
		"FROM {$engine->config['table_prefix']}bad_behavior l ");

	$results		= $engine->load_all(
		"SELECT log_id, ip, host, date, request_method, request_uri, server_protocol, http_headers, user_agent, user_agent_hash, request_entity, status_key ".
		"FROM `" . $bb_table . "` ".
		"WHERE 1=1 " . $where .
		"ORDER BY `log_id` DESC ".
		"LIMIT {$pagination['offset']}, $limit");

	// Display rows to the user
	?>

<h2>Log</h2>
<?php
	echo $engine->form_open('bb2_manage', '', 'post', true, '', '');
?>

	<p class="right">See also: <a href="<?php echo $engine->href()."&amp;setting=bb2_summary" ?>">Summary</a> | Log | <a href="<?php echo $engine->href()."&amp;setting=bb2_options" ?>">Settings</a> | <a href="<?php echo $engine->href()."&amp;setting=bb2_whitelist" ?>">Whitelist</a></p>


<div class="alignleft">
<?php if ($count['n'] < $totalcount['n']): ?>
Displaying <strong><?php echo $count['n']; ?></strong> of <strong><?php echo $totalcount['n']; ?></strong> records filtered by:<br/>
<?php if (isset($_GET['status_key'])	&& $_GET['status_key'])		echo 'Status [<a href="?mode=badbehavior&amp;setting=bb2_manage' . '' . '">X</a>] '; ?>
<?php if (isset($_GET['blocked'])		&& $_GET['blocked'])		echo 'Blocked [<a href="?mode=badbehavior&amp;setting=bb2_manage' . '' . '">X</a>] '; ?>
<?php if (isset($_GET['permitted'])		&& $_GET['permitted'])		echo 'Permitted [<a href="?mode=badbehavior&amp;setting=bb2_manage' . '' . '">X</a>] '; ?>
<?php if (isset($_GET['ip'])			&& $_GET['ip'])				echo 'IP [<a href="?mode=badbehavior&amp;setting=bb2_manage' . '' . '">X</a>] '; ?>
<?php if (isset($_GET['user_agent'])	&& $_GET['user_agent'])		echo 'User Agent [<a href="?mode=badbehavior&amp;setting=bb2_manage' . '' . '">X</a>] '; ?>
<?php if (isset($_GET['request_method']) && $_GET['request_method']) echo 'GET/POST [<a href="?mode=badbehavior&amp;setting=bb2_manage' . '' . '">X</a>] '; ?>
<?php else: ?>
Displaying all <strong><?php echo $totalcount['n']; ?></strong> records<br/>
<?php endif; ?>
<?php if (!isset($_GET['status_key']) && !isset($_GET['blocked'])) { ?><a href="<?php echo $engine->href().'&amp;setting=bb2_manage&amp;blocked=true'; ?>">Show Blocked</a> <?php } ?>
<?php if (!isset($_GET['status_key']) && !isset($_GET['permitted'])) { ?><a href="<?php echo $engine->href().'&amp;setting=bb2_manage&amp;permitted=true'; ?>">Show Permitted</a> <?php } ?>
</div>

<?php
		if (isset($pagination['text']))
		{
			echo '<div class="right">'.( $pagination['text'] == true ? '<small>'.$pagination['text'].'</small>' : '&nbsp;' ).'</div>'."\n";
		}
?>
<table class="formation">
	<thead>
		<tr>
			<th scope="col" class="check-column"></th>
			<th scope="col">IP/Date/Status</th>
			<th scope="col">Headers</th>
			<th scope="col">Entity</th>
		</tr>
	</thead>
	<tbody>
<?php
	// all ip related host names

	if ($results)
	{
		foreach ($results as $result)
		{
			$status_key = bb2_get_response($result['status_key']);

			echo '<tr id="request-' . $result['log_id'] . '"  style="vertical-align:top;" class="lined">'."\n";

			echo '<td scope="row" class="check-column label"><input type="checkbox" name="submit[]" value="' . $result['log_id'] . '" /></td>'."\n";

			$httpbl	= bb2_httpbl_lookup($result['ip']);

			// avoid redundant lookups
			if (empty($result['host']))
			{
				$host = @gethostbyaddr($result['ip']);
				$engine->sql_query(
						"UPDATE {$engine->config['table_prefix']}bad_behavior SET ".
						"host			= '".quote($engine->dblink, $host)."' ".
						"WHERE log_id	= '".(int)$result['log_id']."' ".
						"LIMIT 1");
			}

			$host = $result['host'];

			if (!strcmp($host, $result['ip']))
			{
				$host = '';
			}
			else
			{
				$host .= "<br/>\n";
			}

			// tz offset
			$time_tz = $engine->get_time_tz( strtotime($result['date']) );
			$time_tz = date($engine->config['date_precise_format'], $time_tz);

			echo "<td>".
					"<a href=\"" . '?mode=badbehavior&amp;setting=bb2_manage&amp;ip='.$result['ip'] . "\">" . $result['ip'] . "</a><br/>".
					"$host<br/>\n" .
					$time_tz . "<br/><br/>".
					"<a href=\"" . '?mode=badbehavior&amp;setting=bb2_manage&amp;status_key='.$result['status_key'] . "\" title=\"" .'['.$status_key['response'].'] '.$status_key['explanation']. "\">" . $status_key['log'] . "</a>\n";

			if ($httpbl)
			{
				echo "<br/><br/><a href=\"http://www.projecthoneypot.org/ip_{$result['ip']}\">http:BL</a>:<br/>$httpbl\n";
			}

			echo "</td>\n";

			$headers = str_replace("\n", "<br/>\n", htmlspecialchars($result['http_headers'], ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET));

			if (@strpos($headers, $result['user_agent']) !== FALSE)
			{
				$headers = substr_replace($headers, "<a href=\"" . '?mode=badbehavior&amp;setting=bb2_manage&amp;user_agent='.rawurlencode($result['user_agent_hash']) . "\">" . $result['user_agent'] . "</a>", strpos($headers, $result['user_agent']), strlen($result['user_agent']));
			}

			if (@strpos($headers, $result['request_method']) !== FALSE)
			{
				$headers = substr_replace($headers, "<a href=\"" . '?mode=badbehavior&amp;setting=bb2_manage&amp;request_method='.rawurlencode($result['request_method']) . "\">" . $result['request_method'] . "</a>", strpos($headers, $result['request_method']), strlen($result['request_method']));
			}

			echo "<td>".$headers."</td>\n";
			echo "<td>" . str_replace("\n", "<br/>\n", htmlspecialchars($result['request_entity'], ENT_COMPAT | ENT_HTML401, HTML_ENTITIES_CHARSET)) . "</td>\n";
			echo "</tr>\n";
		}
	}
?>
	</tbody>
</table>

		<?php
		if (isset($pagination['text']))
		{
			echo '<div class="right">'.( $pagination['text'] == true ? '<small>'.$pagination['text'].'</small>' : '' ).'</div>'."\n";
		}
		?>

</form>

<?php
}

function bb2_whitelist(&$engine)
{
	$whitelists = bb2_read_whitelist();

	if (empty($whitelists))
	{
		$whitelists					= array();
		$whitelists['ip']			= array();
		$whitelists['url']			= array();
		$whitelists['useragent']	= array();
	}

	$request_uri = $_SERVER['REQUEST_URI'];

	if (!$request_uri) $request_uri = $_SERVER['SCRIPT_NAME'];	# IIS

	if ($_POST)
	{
		#$_POST = array_map('stripslashes_deep', $_POST);

		if ($_POST['ip']) {
			$whitelists['ip'] = array_filter(preg_split("/\s+/m", $_POST['ip']));
		} else {
			$whitelists['ip'] = array();
		}
		if ($_POST['url']) {
			$whitelists['url'] = array_filter(preg_split("/\s+/m", $_POST['url']));
		} else {
			$whitelists['url'] = array();
		}
		if ($_POST['useragent']) {
			$whitelists['useragent'] = array_filter(preg_split("/[\r\n]+/m", $_POST['useragent']));
		} else {
			$whitelists['useragent'] = array();
		}

		update_option('bad_behavior_whitelist', $whitelists);
?>
	<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>
<?php
	}
?>

	<h2>Whitelist</h2>
<?php
	echo $engine->form_open('bb2_whitelist', '', 'post', true, '', 'setting=bb2_whitelist');
?>
	<p>Inappropriate whitelisting WILL expose you to spam, or cause Bad Behavior to stop functioning entirely! DO NOT WHITELIST unless you are 100% CERTAIN that you should.</p>
	<p class="right">See also: <a href="<?php echo $engine->href()."&amp;setting=bb2_summary" ?>">Summary</a> | <a href="<?php echo $engine->href()."&amp;setting=bb2_manage"; ?>">Log</a> | <a href="<?php echo $engine->href()."&amp;setting=bb2_options" ?>">Settings</a> | Whitelist</p>


	<table class="formation">
		<tr>
			<th colspan="2">
				<br />
				Whitelist
			</th>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="whitelists_ip"><strong>IP Address:</strong><br />
			<small>IP address or CIDR format address ranges to be whitelisted (one per line)</small></label></td>
			<td><textarea cols="24" rows="6" id="whitelists_ip" name="ip"><?php echo implode("\n", $whitelists['ip']); ?></textarea></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="whitelists_url"><strong>URL:</strong><br />
			<small>URL fragments beginning with the / after your web site hostname (one per line)</small></label></td>
			<td><textarea cols="48" rows="6" id="whitelists_url" name="url"><?php echo implode("\n", $whitelists['url']); ?></textarea></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="whitelists_useragent"><strong>User Agent:</strong><br />
			<small>User agent strings to be whitelisted (one per line)</small></label></td>
			<td><textarea cols="48" rows="6" id="whitelists_useragent" name="useragent"><?php echo implode("\n", $whitelists['useragent']); ?></textarea></td>
		</tr>
	</table>
	<br />
	<div class="center"><input type="submit" class="button" name="submit" value="Update &raquo;" /></div>
	</form>
<?php
}

function bb2_options(&$engine)
{
	$settings = bb2_read_settings();


	// update settings
	if (isset($_POST['action']) && $_POST['action'] == 'update')
	{
		#$config['display_stats']				= (string)$_POST['display_stats'];
		$config['strict']						= (string)$_POST['strict'];
		$config['verbose']						= (string)$_POST['verbose'];
		$config['logging']						= (string)$_POST['logging'];
		$config['httpbl_key']					= (string)$_POST['httpbl_key'];
		$config['httpbl_threat']				= (string)$_POST['httpbl_threat'];
		$config['httpbl_maxage']				= (int)$_POST['httpbl_maxage'];
		$config['offsite_forms']				= (string)$_POST['offsite_forms'];
		$config['eu_cookie']					= (int)$_POST['eu_cookie'];
		$config['reverse_proxy']				= (int)$_POST['reverse_proxy'];
		$config['reverse_proxy_header']			= (int)$_POST['reverse_proxy_header'];
		$config['reverse_proxy_addresses']		= (string)$_POST['reverse_proxy_addresses'];

		$engine->_set_config($config, '', true);

		$engine->log(1, '!!Updated email settings!!');
		$engine->set_message('Updated Bad Behavior settings');
		$engine->redirect(rawurldecode($engine->href()));
	}

	$request_uri = $_SERVER['REQUEST_URI'];

	if (!$request_uri)
	{
		$request_uri = $_SERVER['SCRIPT_NAME'];	# IIS
	}

	if ($_POST)
	{
		#$_POST = array_map('stripslashes_deep', $_POST);



		if ($_POST['strict'])
		{
			$settings['strict'] = true;
		}
		else
		{
			$settings['strict'] = false;
		}

		if ($_POST['verbose'])
		{
			$settings['verbose'] = true;
		}
		else
		{
			$settings['verbose'] = false;
		}

		if ($_POST['logging'])
		{
			if ($_POST['logging'] == 'verbose')
			{
				$settings['verbose'] = true;
				$settings['logging'] = true;
			}
			else if ($_POST['logging'] == 'normal')
			{
				$settings['verbose'] = false;
				$settings['logging'] = true;
			}
			else
			{
				$settings['verbose'] = false;
				$settings['logging'] = false;
			}
		}
		else
		{
			$settings['verbose'] = false;
			$settings['logging'] = false;
		}

		if ($_POST['httpbl_key'])
		{
			if (preg_match("/^[a-z]{12}$/", $_POST['httpbl_key']))
			{
				$settings['httpbl_key'] = $_POST['httpbl_key'];
			}
			else
			{
				$settings['httpbl_key'] = '';
			}
		}
		else
		{
			$settings['httpbl_key'] = '';
		}

		if ($_POST['httpbl_threat'])
		{
			$settings['httpbl_threat'] = intval($_POST['httpbl_threat']);
		}
		else
		{
			$settings['httpbl_threat'] = '25';
		}

		if ($_POST['httpbl_maxage'])
		{
			$settings['httpbl_maxage'] = intval($_POST['httpbl_maxage']);
		}
		else
		{
			$settings['httpbl_maxage'] = '30';
		}

		if ($_POST['offsite_forms'])
		{
			$settings['offsite_forms'] = true;
		}
		else
		{
			$settings['offsite_forms'] = false;
		}

		if ($_POST['eu_cookie'])
		{
			$settings['eu_cookie'] = true;
		}
		else
		{
			$settings['eu_cookie'] = false;
		}

		if ($_POST['reverse_proxy'])
		{
			$settings['reverse_proxy'] = true;
		}
		else
		{
			$settings['reverse_proxy'] = false;
		}

		if ($_POST['reverse_proxy_header'])
		{
			$settings['reverse_proxy_header'] = sanitize_text_field(uc_all($_POST['reverse_proxy_header']));
		}
		else
		{
			$settings['reverse_proxy_header'] = 'X-Forwarded-For';
		}

		if ($_POST['reverse_proxy_addresses'])
		{
			$settings['reverse_proxy_addresses'] = preg_split("/[\s,]+/m", $_POST['reverse_proxy_addresses']);
			$settings['reverse_proxy_addresses'] = array_map('sanitize_text_field', $settings['reverse_proxy_addresses']);
		}
		else
		{
			$settings['reverse_proxy_addresses'] = array();
		}

		bb2_write_settings($settings);
?>
	<div id="message" class="updated fade"><p><strong>Options saved</strong></p></div>
<?php
	}
?>
	<div class="wrap">

	<h2>Settings</h2>

<?php
	echo $engine->form_open('bb2_options', '', 'post', true, '', 'setting=bb2_options');
?>
	<input type="hidden" name="action" value="update" />
	<p class="right">See also: <a href="<?php echo $engine->href()."&amp;setting=bb2_summary" ?>">Summary</a> | <a href="<?php echo $engine->href()."&amp;setting=bb2_manage"; ?>">Log</a> | Settings | <a href="<?php echo $engine->href()."&amp;setting=bb2_whitelist" ?>">Whitelist</a></p>

	<table class="formation">
		<tr>
			<th colspan="2">
				<br />
				Logging HTTP request
			</th>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="logging_verbose">Verbose</label></td>
			<td><input type="radio" id="logging_verbose" name="logging" value="verbose" <?php if ($settings['verbose'] && $settings['logging']) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="logging_normal">Normal (recommended)</label></td>
			<td><input type="radio" id="logging_normal" name="logging" value="normal" <?php if ($settings['logging'] && !$settings['verbose']) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="logging_false">Do not log (not recommended)</label></td>
			<td><input type="radio" id="logging_false" name="logging" value="false" <?php if (!$settings['logging']) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<tr>
			<th colspan="2">
				<br />
				Security
			</th>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="strict_checking"><strong>Strict checking</strong><br />
			blocks more spam but may block some people</label></td>
			<td><input type="checkbox" id="strict_checking" name="strict" value="true" <?php if ($settings['strict']) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="offsite_forms"><strong>Allow form postings from other web sites</strong><br />
			required for OpenID; increases spam received</label></td>
			<td><input type="checkbox" id="offsite_forms" name="offsite_forms" value="true" <?php if ($settings['offsite_forms']) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<tr>
			<th colspan="2">
				<br />
				http:BL
			</th>
		</tr>
		<tr class="hl_setting">
			<td colspan="2">
				<p>To use Bad Behavior's http:BL features you must have an <a href="http://www.projecthoneypot.org/httpbl_configure.php?rf=24694" rel="noreferrer">http:BL Access Key</a>.</p>
				<br />
			</td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="httpbl_key">http:BL Access Key</label></td>
			<td><input type="text" size="12" maxlength="12" id="httpbl_key" name="httpbl_key" value="<?php echo $settings['httpbl_key']; ?>" /></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="httpbl_threat">Minimum Threat Level (25 is recommended)</label></td>
			<td><input type="text" size="3" maxlength="3" id="httpbl_threat" name="httpbl_threat" value="<?php echo intval($settings['httpbl_threat']); ?>" /></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="httpbl_maxage">Maximum Age of Data (30 is recommended)</label></td>
			<td><input type="text" size="3" maxlength="3" id="httpbl_maxage" name="httpbl_maxage" value="<?php echo intval($settings['httpbl_maxage']); ?>" /></td>
		</tr>

		<tr>
			<th colspan="2">
				<br />
				European Union Cookie
			</th>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="eu_cookie"><strong>EU cookie handling</strong><br />
			Select this option if you believe Bad Behavior's site security cookie is not exempt from the 2012 EU cookie regulation. <a href="http://bad-behavior.ioerror.us/2012/05/04/eu-cookie-requirement-disclosure/" rel="noreferrer">More info</a></label></td>
			<td><input type="checkbox" id="eu_cookie" name="eu_cookie" value="true" <?php if ($settings['eu_cookie']) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<tr>
			<th colspan="2">
				<br />
				Reverse Proxy/Load Balancer
			</th>
		</tr>
		<tr class="hl_setting">
			<td colspan="2">
				<div>
					If you are using Bad Behavior behind a reverse proxy, load balancer, HTTP accelerator, content cache or similar technology, enable the Reverse Proxy option.
					<p>If you have a chain of two or more reverse proxies between your server and the public Internet, you must specify <em>all</em> of the IP address ranges (in CIDR format) of all of your proxy servers, load balancers, etc. Otherwise, Bad Behavior may be unable to determine the client's true IP address.</p>
					<p>In addition, your reverse proxy servers must set the IP address of the Internet client from which they received the request in an HTTP header. If you don't specify a header, <code><a href="http://en.wikipedia.org/wiki/X-Forwarded-For" rel="noreferrer">X-Forwarded-For</a></code> will be used. Most proxy servers already support X-Forwarded-For and you would then only need to ensure that it is enabled on your proxy servers. Some other header names in common use include <code>X-Real-Ip</code> (nginx) and <code>Cf-Connecting-Ip</code> (CloudFlare).</p>
					<br />
				</div>
			</td>
		</tr>

		<tr class="hl_setting">
			<td class="label"><label for="reverse_proxy">Enable Reverse Proxy</label></td>
			<td><input type="checkbox" id="reverse_proxy" name="reverse_proxy" value="true" <?php if ($settings['reverse_proxy']) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="reverse_proxy_header">Header containing Internet clients' IP address</label></td>
			<td><input type="text" size="32" id="reverse_proxy_header" name="reverse_proxy_header" value="<?php echo $settings['reverse_proxy_header']; ?>" /></td>
		</tr>
		<tr class="lined">
			<td colspan="2"></td>
		</tr>
		<tr class="hl_setting">
			<td class="label"><label for="reverse_proxy_addresses">IP address or CIDR format address ranges for your proxy servers (one per line)</label></td>
			<td><textarea cols="24" rows="6" id="reverse_proxy_addresses" name="reverse_proxy_addresses"><?php echo implode("\n", $settings['reverse_proxy_addresses']); ?></textarea></td>
		</tr>
	</table>
	<br />
	<div class="center"><input type="submit" class="button" name="submit" value="Update &raquo;" /></div>
	</form>
	</div>
<?php
}

if (isset($_POST['action']) && $_POST['action'] == 'purge_badbehavior')
{
	$sql = "TRUNCATE {$engine->config['table_prefix']}badbehavior";
	$engine->sql_query($sql);

	// queries
	$engine->cache->invalidate_sql_cache();

}


#####################################################################################################


?>
	<h1><?php echo $module['title']; ?></h1>
	<br />
	Detects and blocks unwanted Web accesses, deny automated spambots access
	<p>For more information please visit the <a href="http://bad-behavior.ioerror.us/" rel="noreferrer">Bad Behavior</a> homepage.</p>

<?php
if (isset($_GET['setting']) && $_GET['setting'] == 'bb2_options')
	{
		bb2_options($engine);
	}
	else if (isset($_GET['setting']) && $_GET['setting'] == 'bb2_whitelist')
	{
		bb2_whitelist($engine);
	}
	else if (isset($_GET['setting']) && $_GET['setting'] == 'bb2_manage')
	{
		bb2_manage($engine);
	}
	else
	{
		bb2_summary($engine);
	}

?>
		<br />

<?php
}

?>