function fbd_insert(element,URL){
	var win = top.tinymce_window;
    // insert information now
    win.document.getElementById(element).value = URL;
    // for image browsers: update image dimensions
    if(win.ImageDialog){
		if(win.ImageDialog.getImageData) win.ImageDialog.getImageData();
		if(win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage(URL);
    }
    if(win.MinImageDialog){
		if(win.MinImageDialog.getImageData) win.MinImageDialog.getImageData();
		if(win.MinImageDialog.showPreviewImage) win.MinImageDialog.showPreviewImage(URL);
    }
}
function fileBrowserCallBack(field_name, url, type, win) {
	top.tinymce_window = win;
	top.tinymce_browser = fbd_insert;
	if(type == 'file'){
		top.PopupManager.showLinkSelector(url,field_name,'tinymce_browser');
	}
	if(type == 'image'){
		top.PopupManager.showImageSelector(url,field_name,'tinymce_browser');
	}
	if(type == 'doc'){
		top.PopupManager.showDocSelector(url,field_name,'tinymce_browser');
	}
}