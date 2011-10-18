/*-----------------------------------------------------------------------------
	Publish page
-----------------------------------------------------------------------------*/

	jQuery(document).ready(function() {
		// Add a input field for every field instance
		var $fields = jQuery('#contents').find('div.field'),

			// Get JSON data for the fields
			data = Symphony.Context.get('custom_captions'),

			// Template to clone for each field instance
			caption_template = jQuery('<i />');

		if(data != undefined) {
			$fields.each(function(i) {
				var $field = jQuery(this),
					field_id = $field.attr('id').replace(/^field-/i, '');

				if(data[field_id] == undefined) return;

				if($field.find('i').length) {
					$field.find('i').text(data[field_id].caption);
				}
				else {
					template = caption_template.clone();
					template.text(data[field_id].caption);
					$field.find('label > :input:last, label > .frame').before(template);
				}
			});
		}
	});
