<?php

if (!defined('IN_WACKO'))
{
	exit;
}

if (!isset($max))		$max = null;

if ($user_id = $this->get_user_id())
{
	$pref		= $this->db->table_prefix;

	echo $this->_t('MyChangesWatches') .
		' (<a href="' . $this->href('', '', 'mode=mychangeswatches&amp;reset=1') . '#list">' . 
		$this->_t('ResetChangesWatches') . '</a>).<br /><br />';

	$pages = $this->db->load_all(
			"SELECT p.page_id, p.tag, p.modified, w.user_id " .
			"FROM {$pref}page AS p, {$pref}watch AS w " .
			"WHERE p.page_id = w.page_id " .
				"AND p.modified > w.watch_time " .
				"AND w.user_id = '" . (int) $user_id . "' " .
				"AND p.user_id <> '" . (int) $user_id . "' " .
			"GROUP BY p.tag " .
			"ORDER BY p.modified DESC, p.tag ASC "/*.		TODO pagination
			"LIMIT $limit"*/);

	if ((isset($_GET['reset']) && $_GET['reset'] == 1) && $pages == true)
	{
		foreach ($pages as $page)
		{
			$this->db->sql_query(
				"UPDATE {$this->db->table_prefix}watch " .
				"SET watch_time = UTC_TIMESTAMP() " .
				"WHERE page_id = '" . $page['page_id'] . "' " .
					"AND user_id = '" . (int) $user_id . "'");
		}

		$this->http->redirect($this->href('', '', 'mode=mychangeswatches') . '#list');
	}

	if ($pages == true)
	{
		foreach ($pages as $page)
		{
			if (!$this->db->hide_locked || $this->has_access('read', $page['page_id']))
			{
				echo '<small>(' . $this->compose_link_to_page($page['tag'], 'revisions', $this->get_time_formatted($page['modified']), 0, $this->_t('History')) .
					')</small> ' . $this->compose_link_to_page($page['tag'], '', '', 0) . "<br />\n";
			}
		}
	}
	else
	{
		echo '<em>' . $this->_t('NoChangesWatches') . '</em>';
	}
}
else
{
	echo '<em>' . $this->_t('NotLoggedInWatches') . '</em>';
}
