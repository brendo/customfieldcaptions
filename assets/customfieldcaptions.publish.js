/*-----------------------------------------------------------------------------
	Publish page
-----------------------------------------------------------------------------*/

	jQuery(document).ready(function() {
		// Add a input field for every field instance
		var $fields = jQuery('#contents').find('div.field'),

			// Get JSON data for the fields
			data = Symphony.Context.get('custom_captions'),

			// Template to clone for each field instance
			caption_template = jQuery('<span />').addClass('cc');

		if(data === undefined) return;

		$fields.each(function(i) {
			var $field = jQuery(this),
				field_id = $field.attr('id').replace(/^field-/i, ''),
				$inputs = $field.find('label > :input');
				$label = $field.find('label > :input:last, label > .frame');

			if(isNaN(parseInt(field_id)) || data[field_id].caption == undefined) return;

			template = caption_template.clone();
			template.html(data[field_id].caption);

			if (!$label.length || $inputs.length > 1) {
				$field.find('label:first').append(template);
			} else {
				$label.before(template);
			}
		});
	});
