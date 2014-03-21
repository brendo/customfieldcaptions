/*-----------------------------------------------------------------------------
	Publish page
-----------------------------------------------------------------------------*/

	jQuery(document).ready(function() {

		// Add an input field for every field instance
		var $fields = jQuery('#contents').find('div.field'),

			// Get JSON data for the fields
			data = Symphony.Context.get('custom_captions'),

			// Template to clone for each field instance
			caption_template = jQuery('<span />').addClass('cc');

		if(data === undefined) return;

		$fields.each(function(i) {

			// Field variables
			var $field = jQuery(this),
				field_id = $field.attr('id').replace(/^field-/i, ''),

			// Get the textnodes inside the label element
				textNodes = $field.find('label').contents().filter( isTextNode ),
				firstTextNode = textNodes.first();

			if(isNaN(parseInt(field_id)) || data[field_id].caption == undefined) return;

			template = caption_template.clone();
			template.html(data[field_id].caption);
			
			// Append the content after the first textNode
			firstTextNode.after(template)

		});

		// Filter
		function isTextNode(){
			return( this.nodeType === 3 );
		}

	});