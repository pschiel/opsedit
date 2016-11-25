var editor;
var currentdir;
var currentfile;
var buffers = [];
var homepath = 'C:/Web';

function cmd_ls(path, refresh) {
	if (refresh === undefined) {
		$('#statusbar').html('Listing directory...');
	}
	$.ajax({
		type: 'get',
		url: 'index.php',
		data: {
			cmd: 'ls',
			path: path
		},
		success: function(data) {
			data = $.parseJSON(data);
			$('#sidebar').html(data.content);
			if (refresh === undefined) {
				$('#statusbar').html('Changed directory to ' + data.path);
				currentdir = data.path;
			}
		}
	});
}

function cmd_edit(path) {
	$('#statusbar').html('Loading file...');
	$.ajax({
		type: 'get',
		url: 'index.php',
		data: {
			cmd: 'edit',
			path: path
		},
		success: function(data) {
			data = $.parseJSON(data);
			editor.setValue(data.content);
			editor.setOption('mode', data.mode);
			$('#statusbar').html('Loaded ' + data.path);
			currentfile = data.path;
		}
	});
}

function cmd_refresh() {
	cmd_ls(currentdir, true);
}

$(function() {

	editor = CodeMirror.fromTextArea(document.getElementById("editarea"), {
		lineNumbers: true,
		matchBrackets: true,
		mode: "application/x-httpd-php",
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		height: "100%"
	});
	
	$('body').on('click', '#sidebar a.dir, #sidebar a.path', function() {
		cmd_ls($(this).attr('href'));
		return false;
	});
	
	$('body').on('click', '#sidebar a.file', function() {
		cmd_edit($(this).attr('href'));
		return false;
	});
	
	$('#toolbar li').hover(
		function () {
			$(this).addClass('ui-state-hover');
		},
		function () {
			$(this).removeClass('ui-state-hover');
		}
		);
		
	$('#toolbar .cmd-new').click(function() {
		alert('not implemented');
	});
		
	$('#toolbar .cmd-upload').click(function() {
		$('#uploadfile').click();
	});
	
	$('#uploadfile').change(function() {
		$('#upload').ajaxForm(function(data) {
			data = $.parseJSON(data);
			$('#statusbar').html(data.message);
			cmd_refresh();
		});
		$('#uploadpath').val(currentdir);
		$('#upload').submit();
	});
	
	$('#toolbar .cmd-home').click(function() {
		cmd_ls(homepath);
	});
		
	$('#toolbar .cmd-save').click(function() {
		if (!currentfile) {
			alert('No file open');
			return;
		}
		$('#statusbar').html('Saving...');
		$.ajax({
			type: 'post',
			url: 'index.php?cmd=save',
			data: {
				path: currentfile,
				content: editor.getValue()
			},
			success: function(data) {
				data = $.parseJSON(data);
				$('#statusbar').html(data.message);
			}
		});
	});
	
	$('#toolbar .cmd-delete').click(function() {
		alert('not implemented');
	});
		
	$('#toolbar .cmd-search').click(function() {
		alert('not implemented');
	});
		
	$('#toolbar .cmd-refresh').click(function() {
		cmd_refresh();
	});
	
	$('#toolbar .cmd-settings').click(function() {
		alert('not implemented');
	});
		
	$(document).keydown(function(e) {
		if (e.keyCode == 83 && e.ctrlKey) {
			$('#toolbar .cmd-save').click();
			return false;
		}
	});

	cmd_ls(homepath);
	
});