<?php

	Class Extension_CustomFieldCaptions extends Extension {

		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'AdminPagePreGenerate',
					'callback' => '__appendAssets'
				),
				array(
					'page' => '/blueprints/sections/',
					'delegate' => 'FieldPostCreate',
					'callback' => '__saveCustomCaptionToField'
				),
				array(
					'page' => '/blueprints/sections/',
					'delegate' => 'FieldPostEdit',
					'callback' => '__saveCustomCaptionToField'
				),
				array(
					'page' => '/blueprints/sections/',
					'delegate' => 'SectionPostEdit',
					'callback' => '__cleanUp'
				)
			);
		}

		public function install(){
			return Symphony::Database()->query('
				CREATE TABLE IF NOT EXISTS tbl_customcaptions (
					`field_id` INT(4) UNSIGNED DEFAULT NULL,
					`section_id` INT(4) UNSIGNED DEFAULT NULL,
					`caption` TINYTEXT DEFAULT NULL,
					PRIMARY KEY (`field_id`),
					UNIQUE KEY field_id_section_id (`field_id`, `section_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			');
		}

		public function uninstall() {
			Symphony::Database()->query('DROP TABLE IF EXISTS tbl_customcaptions');
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/

		private function addContextToPage(array $data = array()) {
			if(!empty($data)) {
				// Get current Captions and inject into Symphony Context
				Administration::instance()->Page->addElementToHead(
					new XMLElement(
						'script',
						"Symphony.Context.add('custom_captions', " . json_encode($data) . ");",
						array('type' => 'text/javascript')
					), 10000
				);
			}
		}

		public function getCustomCaptionsForSection($section_id = null) {
			if(!is_null($section_id) && !is_numeric($section_id)) {
				$section_id = SectionManager::fetchIDFromHandle($section_id);
			}

			if(is_null($section_id)) return array();

			return Symphony::Database()->fetch(sprintf("
					SELECT field_id, caption
					FROM tbl_customcaptions
					WHERE section_id = %d;",
					$section_id
				),
				'field_id'
			);
		}

	/*-------------------------------------------------------------------------
		Delegate Callbacks
	-------------------------------------------------------------------------*/

		public function __appendAssets(&$context) {
			if(class_exists('Administration')
				&& Administration::instance() instanceof Administration
				&& Administration::instance()->Page instanceof HTMLPage
			) {
				$callback = Administration::instance()->getPageCallback();

				// Section Editor
				if($context['oPage'] instanceof contentBlueprintsSections) {
					$data = $this->getCustomCaptionsForSection($callback['context'][1]);
					$this->addContextToPage($data);

					Administration::instance()->Page->addScriptToHead(URL . '/extensions/customfieldcaptions/assets/customfieldcaptions.sections.js', 101, false);
				}

				// Publish Page
				else if($context['oPage'] instanceof contentPublish) {
					$data = $this->getCustomCaptionsForSection($callback['context']['section_handle']);
					$this->addContextToPage($data);

					Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/customfieldcaptions/assets/customfieldcaptions.publish.css', 'all', 101, false);
					Administration::instance()->Page->addScriptToHead(URL . '/extensions/customfieldcaptions/assets/customfieldcaptions.publish.js', 102, false);
				}
			}
		}

		public function __saveCustomCaptionToField(&$context) {
			$field = $context['field'];

			$data = array(
				'field_id' => $field->get('id'),
				'section_id' => $field->get('parent_section'),
				'caption' => $field->get('custom_caption')
			);

			// Save custom caption against this field
			return Symphony::Database()->insert($data, 'tbl_customcaptions', true);
		}

		public function __cleanUp(&$context) {
			$section_id = (int)$context['section_id'];

			$caption_field_ids = Symphony::Database()->fetchCol("field_id", "SELECT field_id FROM tbl_customcaptions WHERE section_id = " . $section_id);

			$section_schema = FieldManager::fetchFieldsSchema($section_id);
			$section_field_ids = array();
			foreach($section_schema as $field) {
				$section_field_ids[] = $field['id'];
			}

			// If we have any Field ID's that tbl_fields doesn't have
			// remove them, as they have been deleted from the section
			$field_ids = array_diff($caption_field_ids, $section_field_ids);

			if(!empty($field_ids)) {
				Symphony::Database()->delete('`tbl_customcaptions`', 'field_id IN (' . implode(',', $field_ids) . ');');
			}
		}

	}
