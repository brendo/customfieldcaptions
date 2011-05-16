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
				),

			// Inject the template into current $field
			addCaption = function($field, template) {
				$field.find('div.content > :last-child').before(template);
			};

		// Initially run over the all the existing fields
		$fields.each(function(i) {
			var $field = jQuery(this),
				template = field_template.clone();

			var field_id = $field.find(':hidden[name*=id]').val();

			template.find('input')
				.attr('name', 'fields[' + i + '][custom_caption]')

			if(data != undefined) {
				template.find('input').val(data[field_id].caption);
			}

			addCaption($field, template);
		});

		// Listen for when the duplicator changes
		$duplicator.bind('click.duplicator', function() {
			var $field = $duplicator.find('.instance:last');

			// If the field doesn't have a captions field already, add one
			if($field.filter(':has(input[name*=custom_caption])').length == 0) {
				var template = field_template.clone();

				template.find('input')
					.attr('name', 'fields[' + $field.index() + '][custom_caption]')

				addCaption($field, template);
			}
		});
	});