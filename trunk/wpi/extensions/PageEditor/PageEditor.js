function PageEditor(targetId, type, content, pwId) {
	this.$target = $('#' + targetId);
	this.type = type;
	this.content = content;
	this.pwId = pwId;
	this.addEditButton();
}

PageEditor.images = {
	edit:  '/skins/common/images/edit.png',
	ok:  '/skins/common/images/apply.png',
	cancel:  '/skins/common/images/cancel.png',
}

PageEditor.prototype.addEditButton = function() {
	var that = this;
	this.$edit = $('<img class="pageEditBtn" src="' + PageEditor.images.edit + '" />');
	this.$target.before(this.$edit);
	this.$edit.click(function() { that.startEditor(); });
}

PageEditor.prototype.startEditor = function() {
	var that = this;
	
	this.$container = $('<div>');
	
	this.$editor = $('<textarea class="pageEditText">' + this.content + '</textarea>');
	
	this.$container.append(this.$editor);
	
	this.$target.replaceWith(this.$container);
	
	//Replace edit button with save/cancel
	$ok = $('<img src="' + PageEditor.images.ok + '" />');
	$cancel = $('<img src="' + PageEditor.images.cancel + '" />');
	this.$okcancel = $('<div/>');
	this.$okcancel.append($ok);
	this.$okcancel.append($cancel);
	
	$ok.click(function() { that.save(); });
	$cancel.click(function() { that.cancelEditor(); });
	
	this.$edit.remove();
	this.$container.append(this.$okcancel);
}

PageEditor.prototype.cancelEditor = function() {
	this.$container.replaceWith(this.$target);
	this.$okcancel.remove();
	this.addEditButton();
}

PageEditor.prototype.save = function() {
	var that = this;
	
	//Block edit controls
	this.$block = $('<div>').addClass('editblock');
	this.$block.width(this.$container.width() + 'px');
	this.$block.height(this.$container.height() + 'px');
	this.$container.after(this.$block);
	
	this.$block.position({
		my: "left top",
		at: "left top",
		of: this.$container
	});
	
	//Perform save
	sajax_do_call(
		"PageEditor::save", 
		[this.pwId, this.type, this.$editor.val()], 
		function(xhr) { that.afterSave(xhr); }
	);
}

PageEditor.prototype.afterSave = function(xhr) {
	if(this.checkResponse(xhr)) {
		window.location.reload();
	} else {
		this.$block.remove();
	}
}

PageEditor.prototype.checkResponse = function(xhr) {
	if (xhr.readyState == 4){
		if (xhr.status==200) {
			return true;
		} else {
			window.alert("Unable to save: " + xhr.statusText);
		}
	} else {
		window.alert("Unable to save: " + xhr.statusText);
	}
}
