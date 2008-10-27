<?php
	/**
	 * The metadata cache is responsible for caching
	 * metadata for a pathway (e.g. name, organism). This
	 * information is also stored in GPML, but caching it 
	 * prevents that the GPML has to be loaded and parsed
	 * each time the information is queried.
	 */
	class MetaDataCache {
		private static $TAG_PREFIX = "cache-";
		public static $FIELD_NAME = "name";
		public static $FIELD_ORGANISM = "organism";
		public static $FIELD_DEPRECATED = "deprecated";
		
		private $fields;
		private $pathway;
		private $page_id;
		private $tags;
		
		/**
		 * Create a MetaDataCache object for the given
		 * pathway. The constructor will check if the
		 * cache is still up-to-date and updates it if
		 * necessary.
		 */
		public function __construct($pathway) {
			$this->pathway = $pathway;
			$this->page_id = $pathway->getTitleObject()->getArticleId();
			$this->tags = array();
			$this->fields = array(
				self::$FIELD_NAME,
				self::$FIELD_ORGANISM,
				self::$FIELD_DEPRECATED,
			);
			$this->load();
			$this->updateCache(); //Update the cache if necessary
		}
		
		private function createTagName($field) {
			return self::$TAG_PREFIX . $field;
		}
		
		private function load() {
			foreach($this->fields as $f) {
				$this->tags[$f] = new MetaTag($this->createTagName($f), $this->page_id);
			}
		}
		
		/**
		 * Checks for the cache to be valid and updates it if
		 * necessary. The cache will only be updated if
		 * the pathway was changed after the last cache update.
		 */
		public function updateCache() {
			if(!$this->isValid()) {
				$text = Revision::newFromId($this->pathway->getLatestRevision())->getText();
				if($this->pathway->isDeprecated(false)) {
					//leave the old cached values the same
					//but update to set modified timestamp
					$this->doUpdate(self::$FIELD_NAME, $this->getValue(self::$FIELD_NAME));
					$this->doUpdate(self::$FIELD_ORGANISM, $this->getValue(self::$FIELD_ORGANISM));
					//Set deprecated to true
					$this->doUpdate(self::$FIELD_DEPRECATED, $this->pathway->getLatestRevision());
				} else {
					$title = $this->pathway->getPathwayData()->getName();
					$org = $this->pathway->getPathwayData()->getOrganism();
					
					$this->doUpdate(self::$FIELD_NAME, $title);
					$this->doUpdate(self::$FIELD_ORGANISM, $org);
					$this->doDelete(self::$FIELD_DEPRECATED);
				}
			}
		}
		
		private function doDelete($field) {
			$tag = $this->tags[$field];
			if($tag) {
				$tag->setPermissions(array());
				$tag->setUseHistory(false);
				$tag->remove();
			}
		}
		
		private function doUpdate($field, $value) {
			$tag = $this->tags[$field];
			if(!$tag) {
				$tag = new MetaTag($this->createTagName($field), $this->page_id);
				$this->tags[$field] = $tag;
			}
			$tag->setPermissions(array());
			$tag->setUseHistory(false);
			$tag->setText($value);
			$tag->save();
		}
		
		private function isValid() {
			//Just check a single field, since all fields are updated
			//at the same time
			$tag = $this->tags[self::$FIELD_NAME];
			if(!$tag) return false;
			
			$prev = Revision::newFromId(
				$this->pathway->getTitleObject()->getLatestRevID()
			);
			$pmod = $prev->getTimestamp();
			$tmod = $tag->getTimeMod();
			if($pmod > $tmod) {
				return false;
			}
			return true;
		}
		
		/**
		 * Get the cached value for the given field
		 * @param $field A cache field (use one of the $FIELD_* constants)
		 */
		public function getValue($field) {
			$tag = $this->tags[$field];
			if(!$tag) throw new IllegalArgumentException("No cache for field " . $field);
			return $tag->getText();
		}
	}
?>
