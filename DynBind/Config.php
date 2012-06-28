<?php
require "UserBackend.php";
class Config implements UserBackend {
	protected $xmlfile = null;
	/**
	 * @var SimpleXMLElement
	 */
	protected $sxml = null;

	protected $loaded = false;

	public function __construct($xmlfile=null){
		if($xmlfile){
			$this->load($xmlfile);
		}
	}

	public function isLoaded(){
		return $this->loaded;
	}

	/**
	 * @return SimpleXMLElement
	 */
	protected function getUserElements(){
		return $this->getSXML()->users->children();
	}

	/**
	 * @param SimpleXMLElement $ue
	 * return array of string
	 */
	protected function getUsersHostnames(SimpleXMLElement $ue){
		$hosts = array();
		foreach($ue->xpath('hostname') as $host){
			$hosts[] = (string) $host;
		}
		return $hosts;
	}

	/**
	 * @return array of User
	 */
	public function getUsers(){
		$users = array();
		foreach($this->getUserElements() as $ue){ /* var $ue SimpleXMLElement */
			$name = (string) $ue->attributes()->name;
			$password = (string) $ue->attributes()->password;
			$user = new User($name, $password, $this->getUsersHostnames($ue));
			$users[] = $user;
		}
		return $users;
	}

	/**
	 * @throws Exception
	 * @return User
	 */
	public function searchUserByName($username){
		$_username = str_replace("'", '',$username); // just disallow ' in usernames, as proper escaping doesnt't seem to be reasonably possible
		foreach($this->getSXML()->xpath('/config/users/user[@name="'.$_username.'"]') as $ue){ /* var $ue SimpleXMLElement */
			$name = (string) $ue->attributes()->name;
			$password = (string) $ue->attributes()->password;
			$user = new User($name, $password, $this->getUsersHostnames($ue));
			return $user;
		}
		throw new Exception("User $username not found");
	}

	public function getAuthMethod(){
		return (string) $this->getSXML()->general->authmethod;
	}

	public function getUsername(){
		return (string) $this->getSXML()->users->user->attributes()->name;
	}

	public function getPassword(){
		return (string) $this->getSXML()->users->user->attributes()->password;
	}

	public function getNameserver(){
		return (string) $this->getSXML()->zones->zone->nameserver;
	}

	public function getZone(){
		return (string) $this->getSXML()->zones->zone->attributes()->name;
	}

	public function getKeyfile(){
		return (string) (string) $this->getSXML()->zones->zone->keyfile;
	}

	public function getLogfile(){
		return (string) $this->getSXML()->general->logfile;
	}

	public function getDryrun(){
		return (bool) (int) $this->getSXML()->general->dryrun;
	}

	public function load($xmlfile){
		if(!file_exists($xmlfile)){
			throw new Exception("config file doesn't exist");
		}
		$this->xmlfile = $xmlfile;
		$sxml = simplexml_load_file($xmlfile);
		if(!$sxml){
			throw new Exception("could not load config XML file");
		}
		$this->sxml = $sxml;
		$this->loaded = true;
	}

	public function save(){
		$this->getSXML()->asXML($this->xmlfile);
	}

	public function getSXML(){
		if($this->sxml instanceof SimpleXMLElement){
			return $this->sxml;
		}
		throw new Exception("Konfiguration ist nicht geladen");
	}
}