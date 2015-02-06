<?php

require_once MODEL.'user_channel.php';
require_once MODEL.'user_session.php';

class User extends ActiveRecord\Model {

	public function getMainChannel() {
		return UserChannel::find_by_name($this->username);
	}

	public function getOwnedChannels() {
		$first = array($this->getMainChannel());
		$others = UserChannel::all(array('conditions' => array('admins_ids LIKE ? AND name != ?', '%;'.$this->id.';%', $this->username), 'order' => 'id desc'));
		return array_merge($first, $others);
	}

	public function getPostedVideos() {
		$videos = array();
		$channels = $this->getOwnedChannels();

		foreach($channels as $channel)
			foreach($channel->getPostedVideos() as $vid) $videos[] = $vid;

		return $videos;
	}

	public function getSubscriptions($amount='nope') {
		$subscriptions = array();
		$subs = $this->subscriptions;

		if(Utils::stringStartsWith($subs, ';'))
			$subs = substr_replace($subs, '', 0, 1);
		if(Utils::stringEndsWith($subs, ';'))
			$subs = substr_replace($subs, '', -1);

		$subscriptionsArray = explode(';', $subs);

		foreach ($subscriptionsArray as $k => $value) {
			if(!UserChannel::exists($value)) {
				unset($subscriptionsArray[$k]);
			}
		}
		
		if (empty($subscriptionsArray)) {
			return [];
		}
		
		if($amount != 'nope'){
			$amount = count($subscriptionsArray) > $amount = count($subscriptionsArray);
			
			$subscriptions = UserChannel::find($subscriptionsArray, ['limit' => $amount]);				
		}else{
			$subscriptions = UserChannel::find($subscriptionsArray);
		}
			
		return $subscriptions;
	}

	public function getSubscriptionsVideos($amount='nope') {
		$videos = array();
		$subs = $this->subscriptions;

		if(Utils::stringStartsWith($subs, ';'))
			$subs = substr_replace($subs, '', 0, 1);
		if(Utils::stringEndsWith($subs, ';'))
			$subs = substr_replace($subs, '', -1);

		$subs = str_replace(';', ',', $subs);

		$subArrayTemp = explode(',', $subs);
		$subs = "";
		foreach ($subArrayTemp as $sub) $subs .= "'".$sub."',";

		if(Utils::stringEndsWith($subs, ','))
			$subs = substr_replace($subs, '', -1);

		$subs = "(".$subs.")";
		if($subs == '()') return array();

		if($amount != 'nope')
			$vidsToAdd = Video::find_by_sql("SELECT * FROM videos WHERE poster_id IN ".$subs." ORDER BY timestamp DESC LIMIT ".$amount);
		else
			$vidsToAdd = Video::find_by_sql("SELECT * FROM videos WHERE poster_id IN ".$subs." ORDER BY timestamp DESC");


		foreach ($vidsToAdd as $vid) {
			array_push($videos, $vid);
		}

		return $videos;
	}

	public function getSubscriptionsVideosFromChannel($channelId, $amount='nope') {
		$videos = array();
		$subs = $this->subscriptions;

		if(Utils::stringStartsWith($subs, ';'))
			$subs = substr_replace($subs, '', 0, 1);
		if(Utils::stringEndsWith($subs, ';'))
			$subs = substr_replace($subs, '', -1);

		$subs = str_replace(';', ',', $subs);
		$subs = '('.$subs.')';

		if($amount != 'nope')
			$vidsToAdd = Video::find_by_sql("SELECT * FROM videos WHERE poster_id=? ORDER BY timestamp DESC LIMIT ".$amount, array($channelId));
		else
			$vidsToAdd = Video::find_by_sql("SELECT * FROM videos WHERE poster_id=? ORDER BY timestamp DESC", array($channelId));

		foreach ($vidsToAdd as $vid) {
			array_push($videos, $vid);
		}

		return $videos;
	}

	public function getNotifications() {
		$actions = ChannelAction::find_by_sql("SELECT * FROM channels_actions WHERE recipients_ids LIKE ? ORDER BY timestamp DESC", array('%;'.Session::get()->id.';%'));
		return $actions;
	}

	public function setEmail($newMail) {
		$this->email = $newMail;
		$this->save();
	}

	public function setPassword($newPassword) {
		$this->pass = $newPassword;
		$this->save();
	}

	public function setSoundSetting($newSoundSetting) {
		$settings = $this->getSettings();
		if(empty($settings)){
			$settings = array();
		}
		if(is_numeric($newSoundSetting)){
			
		$settings['volume'] = $newSoundSetting;
		$this->settings = json_encode($settings);
		
		$this->save();
		}
	}
	
	public function setDefinitionSetting($newDefinitionSetting) {
		$settings = $this->getSettings();
		if(empty($settings)){
			$settings = array();
		}
		if (is_numeric($newDefinitionSetting)){
		$settings['definition'] = $newDefinitionSetting;
		$this->settings = json_encode($settings);
	
		$this->save();
			
		}
	}
	
	public function setNotificationSettings($newNotificationSetting) {
		$settings = $this->getSettings();
		$notificationssetting = $this->getNotificationSettings();

		foreach ($notificationssetting as $k => $set) {
			
			if(isset($newNotificationSetting[$k])){
				$notificationssetting[$k] = $newNotificationSetting[$k];
			}else{
				$notificationssetting[$k] = 0;
			}
		}
		$settings['notifications'] = $notificationssetting;
		$this->settings = json_encode($settings);
	
		$this->save();
	}
	
	public function setLanguageSetting($lang){
		$settings = $this->getSettings();
		$settings['language'] = $lang;
		$this->settings = json_encode($settings);
		$this->save();
	}
	
	public function getPassword() {
		return $this->pass;
	}
	
	public function getSettings() {
		return json_decode($this->settings, true);
	}
	
	public function getSoundSetting(){
		
		$settings = $this->getSettings();
		if(!isset($settings['volume'])){
			$soundsetting = 1;
		}else{
			$soundsetting = $settings['volume'];
		}
		
		return $soundsetting; 
	}
	
	public function getDefinitionSetting(){
	
		$settings = $this->getSettings();
		
		if(!isset($settings['definition'])){
			$definitionsetting = 0;
		}else{
			$definitionsetting = $settings['definition'];
		}
	
		return $definitionsetting;
	}
	
	public function getNotificationSettings(){
	
		$settings = $this->getSettings();
	
		if(!isset($settings['notifications'])){
			$notificationssetting = array("like" => 1, "comment" => 1, "subscription" => 1, "upload" => 1, "pm" => 1);
		}else{
			$notificationssetting = $settings['notifications'];
		}
	
		return $notificationssetting;
	}
	
	public function getLanguageSetting() {
		$settings = $this->getSettings();
		
		if(!isset($settings['language'])){
			$languagesetting = "auto";
		}else{
			$languagesetting = $settings['language'];
		}
		
		return $languagesetting;
	}
	

	public function hasSubscribedToChannel($channelId) {
		if(UserChannel::exists($channelId)) {
			$subscriptionsStr = $this->subscriptions;

			return strpos($subscriptionsStr, $channelId) !== false;
		}
	}
	
	public function isTeam() {
		$config = new Config(CONFIG.'app.json');
		$config->parseFile();

		return $this->rank == $config->getValue('rankTeam');
	}

	public function isModerator() {
		$config = new Config(CONFIG.'app.json');
		$config->parseFile();

		return $this->rank == $config->getValue('rankModo');
	}

	public function isAdmin() {
		$config = new Config(CONFIG.'app.json');
		$config->parseFile();

		return $this->rank == $config->getValue('rankAdmin');
	}
	
	public function sendWelcomeNotification() {
		
		ChannelAction::create(array(
		'id' => ChannelAction::generateId(6),
		'channel_id' => $this->getMainChannel()->id,
		'recipients_ids' => ";" . $this->id . ";",
		'type' => 'welcome',
		'target' => $this->id,
		'timestamp' => Utils::tps()
		));
	}

	
	public function getLogFails() {
		return json_decode($this->log_fail, true);
	}
	
	public function resetLogFails() {
		$this->log_fail = null;
		$this->save();
	}
	public function addLogFail() {
		
		$log_fail_array = $this->getLogFails();
		if($log_fail_array){ //Si c'est pas nul en bdd
			if(isset($log_fail_array['nb_try']) && $log_fail_array['nb_try'] < Config::getValue_("max_login_try")){ //Si un nb d'essais existe && qu'il est inférieur au max
				
				$log_fail_array["nb_try"]++; //On incrémente et update le last_try
				$log_fail_array["last_try"] = Utils::tps();
				
				if($log_fail_array["nb_try"] >= Config::getValue_("max_login_try")){ //Si on a atteint le max d'essais on update le temps avant autorisation de connexion sinon on met à 0
					$log_fail_array["next_try"] = Utils::tps()+Config::getValue_("login_fail_wait");
				}else{
					$log_fail_array["next_try"] = 0;
				}
				
				
			}else if(!isset($log_fail_array['nb_try'])){
				$log_fail_array = [
						"nb_try" => 1,
						"last_try" => Utils::tps(),
						"next_try" => 0
				];
			}
		}else{
			$log_fail_array = [
					"nb_try" => 1,
					"last_try" => Utils::tps(),
					"next_try" => 0
			];
		}
		
		$this->log_fail = json_encode($log_fail_array);
		$this->save();
	}
	public function isAllowedToAttemptLogin() {
		$log_fail_array = $this->getLogFails();
		if(!$log_fail_array){ return true; }
		if(!isset($log_fail_array["nb_try"])){ return true; }
		if($log_fail_array["nb_try"] < Config::getValue_("max_login_try")){ return true;}

		if(isset($log_fail_array["next_try"]) && $log_fail_array["next_try"]<=Utils::tps()) { return true; }
		
		return false;
		
	}
	public function isIntervalBetweenTwoLogAttemptElapsed() {
		$log_fail_array = $this->getLogFails();
		if(!$log_fail_array){ return false; }
		if(!isset($log_fail_array["last_try"])){ return true; }
		if(Utils::tps() - $log_fail_array["last_try"] > Config::getValue_("login_fail_intervalle")){ return true; }
		
		return false;
		
	}
	
	
	// Static
	
	public static function isNameFree($name) {
		return !User::exists(['username' => $name]);
	}
	
	public static function getNameById($userId) {
		return User::find_by_id($userId)->username;
	}

	public static function getIdByName($username) {
		return User::find_by_username($username)->id;
	}

	public static function isMailRegistered($mail) {
		return User::exists(array('email' => $mail));
	}

	public static function register($username, $password, $mail) {
		$appConfig = new Config(CONFIG.'app.json');
		$appConfig->parseFile();

		$userRank = $appConfig->getValue('rankUser');

		User::create(array(
			'username' => $username,
			'email' => $mail,
			'pass' => password_hash($password, PASSWORD_BCRYPT),
			'subscriptions' => '',
			'reg_timestamp' => Utils::tps(),
			'reg_ip' => $_SERVER['REMOTE_ADDR'],
			'actual_ip' => $_SERVER['REMOTE_ADDR'],
			'rank' => $userRank,
			'settings' => json_encode(array())
		));

		UserChannel::create(array(
			'id' => UserChannel::generateId(6),
			'name' => $username,
			'description' => 'Chaîne de '.$username,
			'owner_id' => User::getIdByName($username),
			'admins_ids' => ';'.User::getIdByName($username).';',
			'avatar' => Config::getValue_('default-avatar'),
			'background' => Config::getValue_('default-background'),
			'subscribers' => 0,
			'subs_list' => 0,
			'views' => 0,
			'verified' => 0
		));
	}
	/**
	 * @return User
	 */
	public static function connect($username, $remember) {
		if(User::find_by_username($username)) {
			$sessid = md5(uniqid());
			$expiration = ($remember) ? Utils::tps() + 365*86400 : Utils::tps() + 24*3600;
			$user = User::find_by_username($username);

			UserSession::create(array('user_id' => $user->id, 'session_id' => $sessid, 'expiration' => $expiration, 'remember' => $remember));
			setcookie('SESSID', $sessid, $expiration);
			return $user;
		}
	}

	public static function logoutCurrent() {
		if(Session::isActive()) {
			UserSession::delete_all(array('conditions' => array('user_id = ?', Session::get()->id)));
			setcookie("SESSID", '', -1);
			Session::set(-1);
		}
	}

}