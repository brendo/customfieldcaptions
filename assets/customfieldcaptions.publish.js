/*-----------------------------------------------------------------------------
	Section Editor
-----------------------------------------------------------------------------*/

	jQuery(document).ready(function() {
		// Add a input field for every field instance
		var $fields = jQuery('#contents').find('div.field'),

			// Get JSON data for the fields
			data = Symphony.Context.get('custom_captions'),

			// Template to clone for each field instance
			caption_template = jQuery('<i />');
			
		if(data !== undefined) {
			$fields.each(function(i) {
				var $field = jQuery(this),
					template = caption_template.clone();

				var field_id = $field.attr('id').replace(/^field-/i, '');

				template.text(data[field_id].caption_formatted);

				$field.find('label > input').before(template);
			});
		}
	});