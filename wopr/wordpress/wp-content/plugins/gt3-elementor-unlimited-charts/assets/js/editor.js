'use strict';


var GT3_Core_Elementor_Control_RepeatableText = elementor.modules.controls.BaseData.extend({
	ui: {
		repeatableTextControl: '.control-gt3-repeatable-text-input',
		repeatableAddControl: '.elementor-control-item-add',
		colors: '.elementor-control-type-gt3-repeatable-text.color_mode .control-gt3-repeatable-blank',
	},

	events: function () {
		return {
			'change @ui.repeatableTextControl': 'onChange',
			'click @ui.repeatableAddControl': 'onAdd',
		};
	},

	initColors: function initColors() {
		var self = this;

		this.ui.colors.wpColorPicker();
	},

	onReady: function () {
		var parent = this;

		var element = jQuery(this.ui.repeatableTextControl);
		var element_val = element.val();
		var color_mode = element.parents('.color_mode').length;
		var obj = [];

		try{
			obj = JSON.parse(element_val);
		}
		catch(e){
			console.log('invalid json');
		  	obj = [];
		}

		var obj_out = '';

		if (obj.length) {
			jQuery( obj ).each(function( index ){
				obj_out += '<div class="control-repeatable-text-item control-repeatable-text-item-'+(index + 1)+(color_mode ? ' control-repeatable-text-item-color_mode' : '')+'" data-repeatable-item="'+(index + 1)+'"'+(color_mode ? ' style="background-color:'+obj[index]+';"' : '')+'>'+obj[index]+'<span class="control-repeatable-text-item_remove"> x</span></div>';


			})
			element.parent().find('.control-repeatable-text-items').html(obj_out);
		}

		jQuery(document).on("click", ".control-repeatable-text-item_remove", function (e) {
	        e.preventDefault();
	        e.stopPropagation();
	        var index = jQuery(this).parents('.control-repeatable-text-item').index();
	        var element = jQuery(this).parents('.control-repeatable-text-items').parent().find('.control-gt3-repeatable-text-input');
	        parent.removeItem(index,element);
	        jQuery(this).parent('.control-repeatable-text-item').remove();
	    });
	    this.initColors();
	},

	onAdd: function (e) {
		var target = e.currentTarget;
		var element = jQuery( target );
		var input = element.parent().find('.control-gt3-repeatable-text-input');
		var input_val = input.val();
		var blank = element.parent().find('.control-gt3-repeatable-blank');
		var blank_val = blank.val();
		var obj = [];
		var items_container = input.parent().find('.control-repeatable-text-items')

		if (blank_val && blank_val.length) {
			try{
			   var obj = JSON.parse(input_val);
			}
			catch(e){
				console.log('invalid json');
			  	var obj = [];
			}
			
			obj.push(blank_val);
			input.val(JSON.stringify(obj)).change();
			this.addNewItem(items_container,blank_val);
		}
		return;
	},

	addNewItem: function (container,value){
		var color_mode = container.parents('.color_mode').length;
		var index = container.find('.control-repeatable-text-item:last-child').attr('data-repeatable-item');
		if (!index) {
			index = 0;
		}
		var item = '<div class="control-repeatable-text-item control-repeatable-text-item-'+(parseInt(index) + 1)+(color_mode ? ' control-repeatable-text-item-color_mode' : '')+'" data-repeatable-item="'+(parseInt(index) + 1)+'"'+(color_mode ? ' style="background-color:'+value+';"' : '')+'>'+value+'<span class="control-repeatable-text-item_remove"> x</span></div>';
		container.append(item);
	},

	removeItem: function (index,element){
		var element_val = element.val();
		if (element_val && element_val.length) {
			try{
			   var obj = JSON.parse(element_val);
			}
			catch(e){
				console.log('invalid json');
			  	var obj = [];
			}
			obj.splice(parseInt(index), 1);
			element.val(JSON.stringify(obj)).change();
		}

	},

	onChange: function (e) {
		var target = e.currentTarget,
			name = target.dataset.setting,
			obj = {},
			val = jQuery(target).val();


		this.setValue(val);
	}
});
elementor.addControlView('gt3-elementor-core-repeatable-text', GT3_Core_Elementor_Control_RepeatableText);
