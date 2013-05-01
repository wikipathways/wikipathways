function PageEditor(targetId, type, content, pwId, userCanEdit) {
	this.$target = $('#' + targetId);
	this.type = type;
	this.content = content;
	this.pwId = pwId;
	this.userCanEdit = userCanEdit;
	this.addEditButton();
}

PageEditor.images = {
	edit:  '/skins/common/images/edit.png',
	ok:  '/skins/common/images/apply.png',
	cancel:  '/skins/common/images/cancel.png'
};

PageEditor.prototype.addEditButton = function() {
	var that = this;
	if(this.userCanEdit) {
		this.$edit = $('<img class="pageEditBtn" src="' + PageEditor.images.edit + '" />')
                	.attr('title', 'Edit ' + this.type);
		this.$target.before(this.$edit);
		this.$edit.click(function() { that.startEditor(); });
	}
};

PageEditor.prototype.startEditor = function() {
	var that = this;

	this.$container = $('<div>');

	if(this.type == "category") {
		this.$editor = $('<div>');
		var categories = $.parseJSON(this.content);
		for(var cat in categories) {
			var checked = "";
			if(categories[cat]) checked = "checked";
			var $check = $('<input type="checkbox" name="category" ' + checked + '/>')
				.attr('value', cat);
			this.$editor.append($check);
			this.$editor.append(cat + '<br>');
		}
	} else if(this.type == "title") {
		this.$editor = $('<input type="text" />')
			.attr('value', this.content)
			.css({
				width: '100%',
				'font-size': 'x-large'
			});
	} else { //Text area by default
		this.$editor = $('<textarea class="pageEditText">' + this.content + '</textarea>');
	}

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
};

PageEditor.prototype.cancelEditor = function() {
	this.$container.replaceWith(this.$target);
	this.$okcancel.remove();
	this.addEditButton();
};

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

	var val = "";
	if(this.type == "category") {
		val = '{';
		this.$editor.find(':checkbox').each(function() {
			var cat = $(this).val();
			var checked = $(this).is(':checked') ? 1 : 0;
			val += '"' + cat + '":' + checked + ',';
		});
		val = val.substring(0, val.length-1) + '}';
	} else {
		val = this.$editor.val();
	}

	//Perform save
	sajax_do_call(
		"PageEditor::save",
		[this.pwId, this.type, val],
		function(xhr) { that.afterSave(xhr); }
	);
};

PageEditor.prototype.afterSave = function(xhr) {
	if(this.checkResponse(xhr)) {
		window.location.reload();
	} else {
		this.$block.remove();
	}
};

PageEditor.prototype.checkResponse = function(xhr) {
	if (xhr.readyState == 4){
		if (xhr.status==200) {
			return true;
		} else {
			window.alert("Unable to save: " + xhr.statusText);
			return false;
		}
	} else {
		window.alert("Unable to save: " + xhr.statusText);
		return false;
	}
};
