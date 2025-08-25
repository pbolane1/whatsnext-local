/* Functions for the insertlist plugin popup */

function preinit() 
{
	// Initialize
	tinyMCE.setWindowArg('mce_windowresize', true);
}


function init() 
{
	tinyMCEPopup.resizeToInnerSize();

	var formObj = document.forms[0];
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	var elm = inst.getFocusElement();
	var action = "insert";
	var html = "";
}

function insertAction() 
{
//	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
//	var elm = inst.getFocusElement();
	var formObj = document.forms[0];

	var html = "<img class='insertlinklist mceNonEditable' list_id='"+getSelectValue(formObj, 'list_id')+"' title='"+document.getElementById('list_id').options[document.getElementById('list_id').selectedIndex].innerHTML+"'/>";
//	html+="<p></p>";
//	if (elm != null && tinyMCE.getAttrib(elm, 'class').indexOf('insertlist') != -1)
//		tinyMCE.activeEditor.dom.remove(elm);
	var ed = tinyMCEPopup.editor, h, f = document.forms[0], st = '';
	tinyMCE.activeEditor.selection.setContent(html);
//	ed.execCommand("mceInsertContent", false, html);

//	tinyMCE._setEventsEnabled(inst.getBody(), false);
	tinyMCEPopup.close();
}

function cancelAction() {
	tinyMCEPopup.close();
}

// While loading
preinit();
