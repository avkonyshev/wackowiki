<?php

if (!defined('IN_WACKO'))
{
	exit;
}

$print_tag_cloud = function ($tags, $method = '', $cluster = '') use (&$tpl)
{
	// TODO: add name space 'category'
	$tag = $this->db->category_page;

	$max_size = 32; // max font size in pixels
	$min_size = 12; // min font size in pixels

	foreach ($tags as $value)
	{
		$qty[] = $value['number'];
	}

	// largest and smallest array values
	$max_qty = max(array_values($qty));
	$min_qty = min(array_values($qty));

	// find the range of values
	$spread = $max_qty - $min_qty;

	if ($spread == 0)
	{
		// we don't want to divide by zero
		$spread = 1;
	}

	// set the font-size increment
	$step = ($max_size - $min_size) / ($spread);

	// context
	$c_tag = $cluster ? ['tag' => $cluster] : [];

	// loop through the tag array
	foreach ($tags as $key => $value)
	{
		// calculate font-size
		// find the $value in excess of $min_qty
		// multiply by the font-size increment ($size)
		// and add the $min_size set above

		$tpl->a_href		= $this->href($method, $tag, ['category_id' => $key] + $c_tag);
		$tpl->a_size		= round($min_size + (($value['number'] - $min_qty) * $step));
		$tpl->a_title		= Ut::perc_replace($this->_t('PagesTaggedWith'), $value['number'], $value['category']);
		$tpl->a_category	= $value['category'];
	}
};

// settings:
//	page		- where to start counting from (defaults to current tag)
//	lang		- categories language if necessary (defaults to current page lang)
//	owner		- page owner
//	sort		- order categories alphabetically or by number ('abc'| 'number')
//	nomark		- display header and fieldset (1) or 0 (default))

// set defaults
$lang		??= $this->page['page_lang'];
$nomark		??= 0;
$owner		??= '';
$page		??= '/';
$sort		??= 'abc';

$tag = $this->unwrap_link($page);

$order = match($sort) {
	'number'	=> 'number DESC',
	default		=> 'c.category ASC',
};

$sql = "SELECT
			c.category_id,
			c.category_lang,
			c.category,
			COUNT(c.category_id) AS number
		FROM
			" . $this->db->table_prefix . "category c
			INNER JOIN " . $this->db->table_prefix . "category_assignment ca ON (c.category_id = ca.category_id)
			INNER JOIN " . $this->db->table_prefix . "page p ON (ca.object_id = p.page_id) " .
			($owner
				? "INNER JOIN " . $this->db->table_prefix . "user u ON (p.user_id = u.user_id) "
				: '' ) .
		"WHERE c.category_lang = " . $this->db->q($lang) . " " .
			"AND ca.object_type_id = 1 " .
			"AND p.deleted <> 1 " .
			($tag
				? "AND ( p.tag = " . $this->db->q($tag) . " OR p.tag LIKE " . $this->db->q($tag . '/%') . " ) "
				: '' ) .
			($owner
				? "AND u.user_name = " . $this->db->q($owner) . " "
				: '' ) .
		"GROUP BY
			c.category
		ORDER BY {$order}";

$tags = $this->db->load_all($sql, true);

if ($tags)
{
	if (!$nomark)
	{
		$tpl->mark		= true;
		$tpl->emark		= true;

		if ($tag)
		{
			$tpl->mark_link		= $this->link('/' . $tag, '', '', '', 0);
			$tpl->mark_cluster	= $this->_t('CategoriesOfCluster');
		}
	}

	foreach ($tags as $cat)
	{
		$this->cloud[$cat['category_id']] = [
			'category'	=> $cat['category'],
			'number'	=> $cat['number']
		];
	}

	$print_tag_cloud($this->cloud, null, $tag);
	unset ($this->cloud);
}
else
{
	$tpl->notaggedpages = true;
}
