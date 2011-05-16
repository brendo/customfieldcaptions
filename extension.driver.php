<?php

	Class Extension_CustomFieldCaptions extends Extension {

		public function about() {
			return array(
				'name' => 'Custom Field Captions',
				'version' => '0.1',
				'release-date' => '2011-05-13',
				'author' => array(
					'name' => 'Brendan Abbott',
					'website' => 'http://bloodbone.ws',
					'email' => 'brendan@bloodbone.ws'
				),
				'description' => 'Adds the ability to have a custom caption for each field on the Publish interface.'
			);
		}

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
					`caption_formatted` TINYTEXT DEFAULT NULL,
					PRIMARY KEY (`field_id`),
					UNIQUE KEY field_id_section_id (`field_id`, `section_id`)
				) ENGINE=MyISAM
			');
		}

		public function uninstall() {
			Symphony::Database()->query('DROP TABLE IF EXISTS tbl_customcaptions');
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/

		private function addContextToPage(Array $data = array()) {
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
			if(is_null($section_id)) return array();

			if(!is_numeric($section_id)) {
				$section_id = Symphony::Database()->fetchVar('id', 0, "SELECT `id` FROM `tbl_sections` WHERE `handle` = '$section_id' LIMIT 1");
			}

			if(is_null($section_id)) return array();

			return Symphony::Database()->fetch("
				SELECT field_id, caption, caption_formatted
				FROM tbl_customcaptions
				WHERE section_id = " . $section_id,
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

					Administration::instance()->Page->addScriptToHead(URL . '/extensions/customfieldcaptions/assets/customfieldcaptions.sections.js', 10001, false);
				}

				// Publish Page
				else if($context['oPage'] instanceof contentPublish) {
					$data = $this->getCustomCaptionsForSection($callback['context']['section_handle']);
					$this->addContextToPage($data);

					Administration::instance()->Page->addScriptToHead(URL . '/extensions/customfieldcaptions/assets/customfieldcaptions.publish.js', 10001, false);
				}
			}
		}

		public function __saveCustomCaptionToField(&$context) {
			$field = $context['field'];

			$data = array(
				'field_id' => $field->get('id'),
				'section_id' => $field->get('parent_section'),
				'caption' => $field->get('custom_caption'),
				'caption_formatted' => General::sanitize($field->get('custom_caption'))
			);

			// Save custom caption against this field
			return Symphony::Database()->insert($data, 'tbl_customcaptions', true);
		}

		public function __cleanUp(&$context) {
			$section_id = $context['section_id'];

			$section_field_ids = Symphony::Database()->fetchCol("id", "SELECT id FROM tbl_fields WHERE parent_section = " . $section_id);
			$caption_field_ids = Symphony::Database()->fetchCol("field_id", "SELECT field_id FROM tbl_customcaptions WHERE section_id = " . $section_id);

			$field_ids = array_diff($caption_field_ids, $section_field_ids);

			if(!empty($field_ids)) {
				Symphony::Database()->delete('`tbl_customcaptions`', 'field_id IN (' . implode(',', $field_ids) . ');');
			}
		}

	}

?>