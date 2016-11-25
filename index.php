<?php

define('DS', '\\');

$modes = array(
	'css' => 'css',
	'js' => 'javascript'
);

function entrysort($a, $b) {
	if ($a['type'] == $b['type']) {
		return ($a['path'] > $b['path']) ? 1 : -1;
	} else {
		return ($a['type'] > $b['type']) ? 1 : -1;
	}
}

function cmd_ls() {
	$path = realpath(urldecode($_GET['path']));
	chdir($path);
	$path = getcwd();
	$handle = opendir($path);
	if (!$handle) {
		die('Cannot open ' . $path);
	}
	$entries = array();
	while (($entry = readdir($handle)) !== false) {
		if ($entry != '.') {
			if (@is_dir($entry) || @is_file($entry)) {
				$type = is_dir($entry) ? 'dir' : 'file';
				$entries[] = array(
					'type' => $type,
					'path' => $path . DS . $entry,
					'label' => $entry
				);
			}
		}
	}
	uasort($entries, 'entrysort');
	$content = '<div class="pathnav">';
	$segments = explode(DS, $path);
	$segpath = '';
	foreach ($segments as $segment) {
		if ($segpath != '') {
			$content .= '<span>' . DS . '</span>';
		}
		$segpath .= $segment . DS;
		$content .= '<a class="path" href="' . urlencode($segpath) . '">' . $segment . '</a>';
	}
	$content .= '</div><div class="listing">';
	foreach ($entries as $entry) {
		$content .= '<a class="' . $entry['type'] . '" href="' . urlencode($entry['path']) . '">' . htmlspecialchars($entry['label']) . '</a>';
    }
	$content .= '</div>';
	echo json_encode(array(
		'content' => $content,
		'path' => $path
	));
	exit;
}

function cmd_edit() {
	global $modes;
	$path = urldecode($_GET['path']);
	$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
	$content = file_get_contents($path);
	if (isset($modes[$ext])) {
		$mode = $modes[$ext];
	} else {
		$mode = 'php';
	}
	echo json_encode(array(
		'content' => $content,
		'mode' => $mode,
		'path' => $path
	));
	exit;
}

function cmd_save() {
	$path = urldecode($_POST['path']);
	$content = $_POST['content'];
	if (file_put_contents($path, $content) === false) {
		$message = 'Could not save ' . $path;
	} else {
		$message = 'Saved ' . $path;
	}
	echo json_encode(array(
		'message' => $message
	));
	exit;
}

function cmd_upload() {
	$path = $_POST['path'] . DS . $_FILES['uploadfile']['name'];
	if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $path)) {
		$message = 'Uploaded ' . $path;
	} else {
		$message = 'Could not upload file';
	}
	echo json_encode(array(
		'message' => $message
	));
	exit;
}

if (isset($_GET['cmd'])) {
	switch ($_GET['cmd']) {
		case 'ls':
			cmd_ls();
			break;
		case 'edit':
			cmd_edit();
			break;
		case 'save':
			cmd_save();
			break;
		case 'upload':
			cmd_upload();
		default:
			exit;
	}
}

?><!DOCTYPE html>
<html lang="en">
	<head>
		<title>opsedit</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/codemirror.css" />
		<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery.contextmenu.css" />
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
		<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
		<script src="http://malsup.github.com/jquery.form.js"></script>
		<script src="https://raw.github.com/joewalnes/jquery-simple-context-menu/master/jquery.contextmenu.js"></script>
		<script type="text/javascript" src="js/codemirror.js"></script>
		<script type="text/javascript" src="js/xml.js"></script>
		<script type="text/javascript" src="js/javascript.js"></script>
		<script type="text/javascript" src="js/css.js"></script>
		<script type="text/javascript" src="js/clike.js"></script>
		<script type="text/javascript" src="js/php.js"></script>
		<script type="text/javascript" src="js/opsedit.js"></script>
	</head>
	<body>
		<div id="wrapper">
			<div id="header">
				<div id="toolbar">
					<ul>
						<li title="New" class="cmd-new ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-document"></span>
						</li>
						<li title="Upload" class="cmd-upload ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-plus"></span>
						</li>
						<li title="Home" class="cmd-home ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-home"></span>
						</li>
						<li title="Save" class="cmd-save ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-disk"></span>
						</li>
						<li title="Copy" class="cmd-save ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-copy"></span>
						</li>
						<li title="Cut" class="cmd-save ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-scissors"></span>
						</li>
						<li title="Paste" class="cmd-save ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-clipboard"></span>
						</li>
						<li title="Delete" class="cmd-delete ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-trash"></span>
						</li>
						<li title="Search" class="cmd-search ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-search"></span>
						</li>
						<li title="Refresh" class="cmd-refresh ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-refresh"></span>
						</li>
						<li title="Settings" class="right cmd-settings ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-gear"></span>
						</li>
					</ul>
				</div>
				<!--<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="7AK83C7EP9TAJ">
					<input id="donate" type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
					<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
				</form>-->
				<form id="upload" enctype="multipart/form-data" action="index.php?cmd=upload" method="post">
					<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
					<input id="uploadpath" type="hidden" name="path" />
					<input id="uploadfile" name="uploadfile" type="file" />
					<input type="submit" />
				</form>
			</div>
			<div id="main">
				<div id="sidebar">
				</div>
				<div id="editor">
					<textarea id="editarea"></textarea>
				</div>
			</div>
			<div id="footer">
				<div id="statusbar">
				</div>
			</div>
		</div>
	</body>
</html>
