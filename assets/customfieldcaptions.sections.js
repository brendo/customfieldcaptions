/*-----------------------------------------------------------------------------
	Language strings
-----------------------------------------------------------------------------*/

	Symphony.Language.add({
		'Custom caption': false
	});

/*-----------------------------------------------------------------------------
	Section Editor
-----------------------------------------------------------------------------*/

	jQuery(document).ready(function() {
		// Add a input field for every field instance
		var $duplicator = jQuery('#fields-duplicator'),
			$fields = $duplicator.find('.instance'),

			// Get JSON data for the fields
			data = Symphony.Context.get('custom_captions'),

			// Template to clone for each field instance
			field_template = jQuery('<label />')
				.text(Symphony.Language.get('Custom caption'))
				.append(
					jQuery('<input />')
					.attr('type', 'text')
				);

		$fields.each(function(i) {
			var $field = jQuery(this),
				template = field_template.clone();

			var field_id = $field.find(':hidden[name*=id]').val();

			template.find('input')
				.attr('name', 'fields[' + i + '][custom_caption]')
				.val(data[field_id].caption);

			$field.find('div.content > :last-child').before(template);
		});
	});