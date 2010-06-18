<?php
/**
 * Utility class to hold information about an organism and 
 * maintain a list of registered organisms.
 */
class Organism {
	private $latinName;
	private $code;
	
	private static $byLatinName = array();
	private static $byCode = array();
	
	public function getLatinName() { return $this->latinName; }
	public function getCode() { return $this->code; }
	
	public static function getByLatinName($name) {
		return self::$byLatinName[$name];
	}
	
	public static function getByCode($code) {
		return self::$byCode[$code];
	}
	
	/**
	 * List all registered organisms.
	 * @return An array where the keys are the latin names and the values
	 * are instances of class Organism.
	 */
	public static function listOrganisms() {
		return self::$byLatinName;
	}
	
	/**
	 * Register a new organism for which pathways can be created.
	 */
	public static function register($latinName, $code) {
		$org = new Organism();
		$org->$latinName = $latinName;
		$org->$code = $code;
		self::$byLatinName[$latinName] = $org;
		self::$byCode[$code] = $org;
	}

	/**
	 * Remove an organism from the registry.
	 */
	public static function remove($org) {
		unset(self::$byLatinName[$org->latinName]);
		unset(self::$byCode[$org->code]);
	}
	
	/**
	 * Register all organisms supported by default on WikiPathways.
	 */
	public static function registerDefaultOrganisms() {
		self::register('Anopheles gambiae', 'Ag');
		self::register('Arabidopsis thaliana', 'At');
		self::register('Bos taurus', 'Bt');
		self::register('Bacillus subtilis', 'Bs');
		self::register('Caenorhabditis elegans', 'Ce');
		self::register('Canis familiaris', 'Cf');
		self::register('Danio rerio', 'Dr');
		self::register('Drosophila melanogaster', 'Dm');
		self::register('Escherichia coli', 'Ec');
		self::register('Equus caballus', 'Qc');
		self::register('Gallus gallus', 'Gg');
		self::register('Homo sapiens', 'Hs'); 
		self::register('Mus musculus', 'Mm');
		self::register('Mycobacterium tuberculosis', 'Mx');
		self::register('Oryza sativa', 'Oj');
		self::register('Pan troglodytes', 'Pt');
		self::register('Rattus norvegicus', 'Rn');
		self::register('Saccharomyces cerevisiae', 'Sc');
		self::register('Sus scrofa', 'Ss');
		self::register('Xenopus tropicalis', 'Xt');
		self::register('Zea mays', 'Zm');
	}
}
//Register the default organisms
Organism::registerDefaultOrganisms();
?>
