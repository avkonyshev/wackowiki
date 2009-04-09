<?php
$message = $this->GetMessage();
header("Content-Type: text/html; charset=".$this->GetCharset());
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->page["lang"] ?>" lang="<?php echo $this->page["lang"] ?>">
<head>
<title><?php echo $this->AddSpaces($this->GetPageTag()).($this->method!="show"?" (".$this->method.")":"");
echo " (@".$this->GetWackoName().")" ?></title>
<?php if ($this->GetMethod() != 'show' || $this->page["latest"] == "N")
echo "<meta name=\"robots\" content=\"noindex, nofollow\" />\n";?>
<meta name="keywords" content="<?php echo $this->GetKeywords(); ?>" />
<meta name="description"
	content="<?php echo $this->GetDescription(); ?>" />
<meta name="language" content="<?php echo $this->page["lang"] ?>" />
<meta http-equiv="content-type"
	content="text/html; charset=<?php echo $this->GetCharset(); ?>" />
<link rel="stylesheet" type="text/css"
	href="<?php echo $this->GetConfigValue("theme_url") ?>css/wacko.css" />
<link rel="shortcut icon"
	href="<?php echo $this->GetConfigValue("theme_url") ?>icons/favicon.ico"
	type="image/x-icon" />
<link rel="alternate" type="application/rss+xml"
	title="<?php echo $this->GetTranslation("RecentChangesRSS");?>"
	href="<?php echo $this->GetConfigValue("base_url");?>xml/recentchanges_<?php echo preg_replace("/[^a-zA-Z0-9]/", "", strtolower($this->GetConfigValue("wacko_name")));?>.xml" />
<link rel="alternate" type="application/rss+xml"
	title="<?php echo $this->GetTranslation("RecentCommentsRSS");?>"
	href="<?php echo $this->GetConfigValue("base_url");?>xml/recentcomment_<?php echo preg_replace("/[^a-zA-Z0-9]/", "", strtolower($this->GetConfigValue("wacko_name")));?>.xml" />
<link rel="alternate" type="application/rss+xml"
	title="<?php echo $this->GetTranslation("HistoryRevisionsRSS");?><?php echo $this->tag; ?> in RSS"
	href="<?php echo $this->href("revisions.xml");?>" />
<?php
// JS files.
// default.js contains common procedures and should be included everywhere
?>
  <script type="text/javascript" src="<?php echo $this->GetConfigValue("base_url");?>js/default.js"></script>
<?php
// protoedit & wikiedit2.js contain classes for WikiEdit editor. We may include them only on method==edit pages
if ($this->method == 'edit')
{
	echo "  <script type=\"text/javascript\" src=\"".$this->GetConfigValue("base_url")."js/protoedit.js\"></script>\n";
	echo "  <script type=\"text/javascript\" src=\"".$this->GetConfigValue("base_url")."js/wikiedit2.js\"></script>\n";
	echo "  <script type=\"text/javascript\" src=\"".$this->GetConfigValue("base_url")."js/autocomplete.js\"></script>\n";
}
?>
<script type="text/javascript"
	src="<?php echo $this->GetConfigValue("base_url");?>js/swfobject.js"></script>
<script type="text/javascript" src="<?php echo $this->GetConfigValue("base_url");?>js/captcha.js"></script>
<?php
if ($user = $this->GetUser())
if ($user["doubleclickedit"] == "Y") {?>
<script type="text/javascript">
   var edit = "<?php echo $this->href("edit");?>";
  </script>
<?php }?>
</head>
<body onload="all_init();<?php if ($message) echo "alert('".$message."');";?>">
<div class="Top<?php if (!$this->GetUser()) echo "LoggedOut";?>">
  <div class="TopRight"><?php echo $this->FormOpen("", $this->GetTranslation("TextSearchPage"), "get"); ?> <span class="nobr"> <?php echo $this->ComposeLinkToPage($this->GetConfigValue("root_page")) ?>&nbsp;|&nbsp; <?php echo $this->Format($this->GetDefaultBookmarks($this->userlang, "site")) ?></span> | <?php echo $this->GetTranslation("SearchText") ?>
    <input
	name="phrase" size="15" class="ShSearch" />
    <?php echo $this->FormClose(); ?> </div>
  <div class="TopLeft">
    <?php if ($this->GetUser()) { ?>
    <img
	src="<?php echo $this->GetConfigValue("theme_url") ?>icons/role.gif"
	hspace="5" vspace="5" width="9" height="15" alt="" /><span class="nobr"><?php echo $this->GetTranslation("YouAre")." ".$this->Link($this->GetUserName()) ?></span> <small>( <span class="nobr Tune">
    <?php
echo $this->ComposeLinkToPage($this->GetTranslation("YouArePanelLink"), "", $this->GetTranslation("YouArePanelName"), 0); ?>
    | <a
	onclick="return confirm('<?php echo $this->GetTranslation("LogoutAreYouSure");?>');"
	href="<?php echo $this->Href("","Login").($this->config["rewrite_mode"] ? "?" : "&amp;");?>action=logout&amp;goback=<?php echo $this->SlimUrl($this->tag);?>"><?php echo $this->GetTranslation("LogoutLink"); ?></a></span> )</small>
    <?php } else { ?>
    <table cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td>
        	<?php echo $this->FormOpen("", "Login", "post"); ?>
      		<input type="hidden" name="action" value="login" />
        	<img
				src="<?php echo $this->GetConfigValue("theme_url") ?>icons/norole.gif"
				hspace="5" vspace="5" width="9" height="15" alt="" /></td>
        <td><strong><?php echo $this->GetTranslation("LoginWelcome") ?>:&nbsp;</strong> </td>
        <td><input type="text" name="name" size="18" /></td>
        <td>&nbsp;&nbsp;&nbsp;<?php echo $this->GetTranslation("LoginPassword") ?>:&nbsp; </td>
        <td><input type="hidden" name="goback"
			value="<?php echo $this->SlimUrl($this->tag);?>" />
          <input
			type="password" name="password" size="8" />
          &nbsp;</td>
        <td><input class="OkBtn_Top"
			onmouseover='this.className="OkBtn_Top_";'
			onmouseout='this.className="OkBtn_Top";' style="font-size: 13px"
			type="submit" value="&nbsp;&nbsp;&raquo;&nbsp;&nbsp;" />
        </td>
      </tr>
      <?php echo $this->FormClose(); ?>
    </table>
    <?php } ?>
  </div>
  <br clear="all" />
  <img src="<?php echo $this->GetConfigValue("base_url") ?>images/z.gif"
	width="1" height="1" alt="" /></div>
<div class="TopDiv"><img
	src="<?php echo $this->GetConfigValue("base_url");?>images/z.gif"
	width="1" height="1" alt="" /></div>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
  <tr>
    <td valign="top" class="Bookmarks">&nbsp;&nbsp;<strong><?php echo $this->GetTranslation("Bookmarks") ?>:</strong>&nbsp;&nbsp;</td>
    <td width="100%" class="Bookmarks"><?php //echo $this->GetBookmarksFormatted(); ?>
      <?php echo $this->Format(implode(" | ", $this->GetBookmarks())); ?>&nbsp;&nbsp;</td>
  </tr>
</table>
<div class="TopDiv2"><img
	src="<?php echo $this->GetConfigValue("base_url");?>images/z.gif"
	width="1" height="1" alt="" /></div>
<div class="Wrapper"
<?php if ($this->method == "edit") echo "style=\"margin-bottom:0;padding-bottom:0\""?>>
<div class="Print">
  <?php if ($this->GetUser()) { ?>
  <?php echo ($this->IsWatched($this->GetUserName(), $this->GetPageTag()) ?
      "<a href=\"".$this->href("watch")."\">".$this->GetTranslation("RemoveWatch")."</a>" :
      "<a href=\"".$this->href("watch")."\">".$this->GetTranslation("SetWatch")."</a>" ) ?> ::
  <?php if (!in_array($this->GetPageSuperTag(),$this->GetBookmarkLinks())) {?>
  <a href="<?php echo $this->Href('', '', "addbookmark=yes")?>"><img
	src="<?php echo $this->GetConfigValue("theme_url") ?>icons/bookmark.gif"
	width="12" height="12"
	alt="<?php echo $this->GetTranslation("AddToBookmarks") ?>" /></a> ::
  <?php } else { ?>
  <a
	href="<?php echo $this->Href('', '', "removebookmark=yes")?>"><img
	src="<?php echo $this->GetConfigValue("theme_url") ?>icons/unbookmark.gif"
	width="12" height="12"
	alt="<?php echo $this->GetTranslation("RemoveFromBookmarks") ?>" /></a> ::
  <?php } }
?>
  <?php echo"<a href=\"".$this->href("print")."\" target=\"_blank\">" ?><img
	src="<?php echo $this->GetConfigValue("theme_url") ?>icons/print.gif"
	width="21" height="20"
	alt="<?php echo $this->GetTranslation("PrintVersion") ?>" /></a> :: <?php echo"<a href=\"".$this->href("msword")."\" target=\"_blank\">" ?><img
	src="<?php echo $this->GetConfigValue("theme_url") ?>icons/msword.gif"
	width="16" height="16"
	alt="<?php echo $this->GetTranslation("MsWordVersion") ?>" /></a></div>
<div class="header">
  <h1><span class="Main"><?php echo $this->config["wacko_name"] ?>:</span> <?php echo $this->GetPagePath(); ?> <a class="Search"
	title="<?php echo $this->GetTranslation("SearchTitleTip")?>"
	href="<?php echo $this->config["base_url"] ?>TextSearch<?php echo ($this->config["rewrite_mode"] ? "?" : "&amp;");?>phrase=<?php echo urlencode($this->GetPageTag()); ?>">...</a> </h1>
  <?php if (($this->method != "edit") || !$this->HasAccess("write")) { ?>
  <div style="background-image:url(<?php echo $this->GetConfigValue("theme_url") ?>icons/shade2.gif);" class="Shade"><img
	src="<?php echo $this->GetConfigValue("theme_url") ?>icons/shade1.gif"
	width="106" height="6" alt="" /></div>
  <?php } ?>
</div>
