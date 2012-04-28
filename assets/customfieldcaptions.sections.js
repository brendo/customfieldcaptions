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
				var $input = $field.find('div.content label:first'),
					$group = $input.closest('.group');

				if($group.length) {
					$group.after(template);
				}
				else {
					$input.after(template);
				}
			},

			// Inject template on the fly (as new fields as added)
			insertCaption = function($field) {
				// If the field doesn't have a captions field already, add one
				if($field.filter(':has(input[name*=custom_caption])').length == 0) {
					var template = field_template.clone();

					template.find('input')
						.attr('name', 'fields[' + ($field.index() - 1) + '][custom_caption]')

					addCaption($field, template);
				}
			};

		// Initially run over the all the existing fields
		$fields.each(function(i) {
			var $field = jQuery(this),
				field_id = $field.find(':hidden[name*=id]').val(),
				template = field_template.clone();

			template.find('input')
				.attr('name', 'fields[' + i + '][custom_caption]')

			if(data != undefined) {
				template.find('input').val(data[field_id].caption);
			}

			addCaption($field, template);
		});

		// Listen for when the duplicator changes [2.2.5]
		$duplicator.bind('click.duplicator', function() {
			insertCaption($duplicator.find('.instance:last'));
		});

		// Listen for when the duplicator changes [2.3]
		jQuery('.frame').on('constructshow.duplicator', 'li', function() {
			insertCaption(jQuery(this));
		});

	});