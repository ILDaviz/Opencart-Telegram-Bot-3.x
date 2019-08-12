<?php
/**
 * Telegram webhook comuinication
 * @version 172
 * @author David Galet
 * @link http://www.davidev.it/
 *
 */
class ControllerExtensionModuleTelegramBotCore extends Controller {

	private $error = array();
	private $base_dir = '';
	private $version = 1;

	public function __construct($registry) {
		parent::__construct($registry);
		$registry->set('telegram', new Telegram($registry));
		$this->base_dir = str_replace('/system/', '/',DIR_SYSTEM);
	}

	public function index() {
		$this->load->language('extension/module/telegram_bot_core');
		$this->load->model('extension/module/telegram_core');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$output = array();
			$output = $this->request->post;
			$output['telegram_bot_core_verison'] = $this->version;
			$catalog = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			$url = $catalog.'index.php?route=extension/module/telegram/webhook';
			$this->telegram->setKeybot($this->request->post['telegram_bot_core_token']);
			$this->telegram->setWebhook($url);
			$output['telegram_bot_core_url_webhook'] = $url;
			$this->model_setting_setting->editSetting('telegram_bot_core', $output);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['token'])) {
			$data['error_token'] = $this->error['token'];
		} else {
			$data['error_token'] = '';
		}

		if (isset($this->error['username'])) {
			$data['error_username'] = $this->error['username'];
		} else {
			$data['error_username'] = '';
		}

		if (isset($this->error['existence'])) {
			$data['error_existence'] = $this->error['existence'];
		} else {
			$data['error_existence'] = '';
		}

		if (isset($this->error['coincidence'])) {
			$data['error_coincidence'] = $this->error['coincidence'];
		} else {
			$data['error_coincidence'] = '';
		}

		if (isset($this->error['https'])) {
			$data['error_https'] = $this->error['https'];
		} else {
			$data['error_https'] = '';
		}

		if (isset($this->error['presence'])) {
			$data['error_presence'] = $this->error['presence'];
		} else {
			$data['error_presence'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/telegram_bot_core', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/telegram_bot_core', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/telegram_bot_core', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['telegram_bot_core_status'])) {
			$data['telegram_bot_core_status'] = $this->request->post['telegram_bot_core_status'];
		} elseif ($this->config->get('telegram_bot_core_status')) {
			$data['telegram_bot_core_status'] = $this->config->get('telegram_bot_core_status');
		} else {
			$data['telegram_bot_core_status'] = '';
		}

		if (isset($this->request->post['telegram_bot_core_token'])) {
			$data['totelegram_bot_core_tokenken'] = $this->request->post['telegram_bot_core_token'];
		} elseif ($this->config->get('telegram_bot_core_token')) {
			$data['telegram_bot_core_token'] = $this->config->get('telegram_bot_core_token');
		} else {
			$data['telegram_bot_core_token'] = '';
		}

		if (isset($this->request->post['telegram_bot_core_thanks_message'])) {
			$data['telegram_bot_core_thanks_message'] = $this->request->post['telegram_bot_core_thanks_message'];
		} elseif ($this->config->get('telegram_bot_core_thanks_message')) {
			$data['telegram_bot_core_thanks_message'] = $this->config->get('telegram_bot_core_thanks_message');
		} else {
			$data['telegram_bot_core_thanks_message'] = $this->language->get('thanks_message');
		}

		if (isset($this->request->post['telegram_bot_core_not_found'])) {
			$data['telegram_bot_core_not_found'] = $this->request->post['telegram_bot_core_not_found'];
		} elseif ($this->config->get('telegram_bot_core_not_found')) {
			$data['telegram_bot_core_not_found'] = $this->config->get('telegram_bot_core_not_found');
		} else {
			$data['telegram_bot_core_not_found'] = $this->language->get('command_not_found');
		}

		if (isset($this->request->post['telegram_bot_core_start_heder'])) {
			$data['telegram_bot_core_start_heder'] = $this->request->post['telegram_bot_core_start_heder'];
		} elseif ($this->config->get('telegram_bot_core_start_heder')) {
			$data['telegram_bot_core_start_heder'] = $this->config->get('telegram_bot_core_start_heder');
		} else {
			$data['telegram_bot_core_start_heder'] = $this->language->get('start_head');
		}

		if (isset($this->request->post['telegram_bot_core_start_footer'])) {
			$data['telegram_bot_core_start_footer'] = $this->request->post['telegram_bot_core_start_footer'];
		} elseif ($this->config->get('telegram_bot_core_not_found')) {
			$data['telegram_bot_core_start_footer'] = $this->config->get('telegram_bot_core_start_footer');
		} else {
			$data['telegram_bot_core_start_footer'] = $this->language->get('start_footer');
		}

		if (isset($this->request->post['telegram_bot_core_help_header'])) {
			$data['telegram_bot_core_help_header'] = $this->request->post['telegram_bot_core_help_header'];
		} elseif ($this->config->get('telegram_bot_core_not_found')) {
			$data['telegram_bot_core_help_header'] = $this->config->get('telegram_bot_core_help_header');
		} else {
			$data['telegram_bot_core_help_header'] = $this->language->get('help_head');
		}

		if (isset($this->request->post['telegram_bot_core_help_footer'])) {
			$data['telegram_bot_core_not_found'] = $this->request->post['telegram_bot_core_help_footer'];
		} elseif ($this->config->get('telegram_bot_core_help_footer')) {
			$data['telegram_bot_core_help_footer'] = $this->config->get('telegram_bot_core_help_footer');
		} else {
			$data['telegram_bot_core_help_footer'] = $this->language->get('help_footer');
		}

		if (isset($this->request->post['telegram_bot_core_name'])) {
			$data['telegram_bot_core_name'] = $this->request->post['telegram_bot_core_name'];
		} elseif ($this->config->get('telegram_bot_core_name')) {
			$data['telegram_bot_core_name'] = $this->config->get('telegram_bot_core_name');
		} else {
			$data['telegram_bot_core_name'] = '';
		}

		if (isset($this->request->post['telegram_bot_core_store'])) {
			$data['telegram_bot_core_store'] = $this->request->post['telegram_bot_core_store'];
		} elseif ($this->config->get('telegram_bot_core_store')) {
			$data['telegram_bot_core_store'] = $this->config->get('telegram_bot_core_store');
		} else {
			$data['telegram_bot_core_store'] = '';
		}

		if (isset($this->request->post['telegram_bot_core_language'])) {
			$data['telegram_bot_core_language'] = $this->request->post['telegram_bot_core_language'];
		} elseif ($this->config->get('telegram_bot_core_language')) {
			$data['telegram_bot_core_language'] = $this->config->get('telegram_bot_core_language');
		} else {
			$data['telegram_bot_core_language'] = '';
		}

		if (isset($this->request->post['telegram_bot_core_command_value'])) {
			$command_values = $this->request->post['telegram_bot_core_command_value'];
		} elseif ($this->config->get('telegram_bot_core_command_value')) {
			$command_values = $this->config->get('telegram_bot_core_command_value');
		} else {
			$files = glob($this->base_dir . 'catalog/controller/extension/module/telegram/command/*.php');
			$command_values = array();
			if ($files) {
				foreach ($files as $file) {
					$command = basename($file, '.php');
					$command_values[] = array(
						'command_file' 				=> $command,
						'name' 						=> $command,
						'command_value_description' => '',
						'status'					=> '0',
					);
				}
			}
		}

		$data['command_values'] = array();
		foreach ($command_values as $command_value) {
			$files = glob($this->base_dir . 'catalog/controller/extension/module/telegram/command/*.php');
			$command_values = array();
			if ($files) {
				foreach ($files as $file) {
					$command = basename($file, '.php');
					if ($command_value['command_file'] !== $command ) {
						$data['command_values'][] = array(
							'command_file' 				=> $command,
							'command_value_description' => '',
							'name' 						=> $command,
							'status'					=> '0',
						);
					} else {
						$data['command_values'][] = array(
							'command_file'         		=> $command_value['command_file'],
							'command_value_description' => $command_value['command_value_description'],
							'name'               		=> $command_value['command_file'],
							'status'					=> $command_value['status']
						);
					}
				}
			}
		}
		
		if (isset($this->request->post['telegram_bot_core_trigger_value'])) {
			$trigger_values = $this->request->post['telegram_bot_core_trigger_value'];
		} elseif ($this->config->get('telegram_bot_core_trigger_value')) {
			$trigger_values = $this->config->get('telegram_bot_core_trigger_value');
		} else {
			$trigger_values = '';
		}

		$data['trigger_values'] = array();
		if (!empty($trigger_values)) {
			foreach ($trigger_values as $trigger_value) {
				$data['trigger_values'][] = array(
					'trigger_value_keyword_a'   => $trigger_value['trigger_value_keyword_a'],
					'trigger_value_keyword_b' 	=> $trigger_value['trigger_value_keyword_b'],
					'role'               		=> $trigger_value['role'],
					'trigger_value_description'	=> $trigger_value['trigger_value_description']
				);
			}
		}

		//Get list store.
		$data['stores'] = array();
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
			'url'      => $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG,
			'edit'     => $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true)
		);
		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name'],
				'url'      => $result['url'],
				'edit'     => $this->url->link('setting/store/edit', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $result['store_id'], true)
			);
		}

		if ($this->config->get('telegram_bot_core_cron_url')) {
			$data['telegram_bot_core_cron_url'] = $this->config->get('telegram_bot_core_cron_url');
		} else {
			$data['telegram_bot_core_cron_url'] = '';
		}

		if ($this->config->get('telegram_bot_core_url_webhook')) {
			$data['telegram_bot_core_url_webhook'] = $this->config->get('telegram_bot_core_url_webhook');
		} else {
			$data['telegram_bot_core_url_webhook'] = '';
		}

		//Estrazione lingue!
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/telegram_bot_core', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/telegram_bot_core')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['telegram_bot_core_token']) {
			$this->error['token'] = $this->language->get('error_token');
		}

		if (!$this->request->post['telegram_bot_core_name']) {
			$this->error['name'] = $this->language->get('error_username');
		}

		if ($this->request->post['telegram_bot_core_token'] && $this->request->post['telegram_bot_core_name']){
			$getMe = $this->telegram->getMe($this->request->post['telegram_bot_core_token']);
			if ($getMe['result']['is_bot'] == 0) {
				$this->error['existence'] = $this->language->get('error_existence');
			}
			if ($getMe['result']['username'] !== $this->request->post['telegram_bot_core_name'] ) {
				$this->error['coincidence'] = $this->language->get('error_username_coincidence');
			}
		}

		if ($this->request->server['HTTPS'] == false) {
			$this->error['https'] = $this->language->get('error_https');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('extension/module/telegram_core');
		if($this->config->get('telegram_bot_core_verison')){
			if ($this->config->get('telegram_bot_core_verison') > 1 ) {
				# code...
			}
		} else {
			$this->model_extension_module_telegram_core->install();
		}
	}

	public function uninstall() {
		$this->load->model('extension/module/telegram_core');
		$this->model_extension_module_telegram_core->uninstall();
		//deleteSetting('telegram_bot_core');
	}

	public function chat(){
		$this->load->model("user/user");
		$this->load->model('extension/module/telegram_core');
		$this->load->language('extension/module/telegram_bot_chat');
		$this->telegram->setKeybot($this->config->get('telegram_bot_core_token'));
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data = array();

		if (isset($this->request->get['chat_id'])) {
			$chat_id = $this->request->get['chat_id'];
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		/** -------------------------------- */
		/** If is present new message estract from archivided message */
		/** -------------------------------- */
		$list_data =  $this->model_extension_module_telegram_core->getLogs();
		foreach ($list_data as $chat_data) {
			if ($chat_data['event'] == 'message_input') {
				$result = $this->model_extension_module_telegram_core->getMessageInput($chat_data['message_id']);
				if ($result['displayed'] == 0 ) {
					$this->model_extension_module_telegram_core->unarchiviedChat($chat_data['chat_id']);
				}
			} else {
				$result = $this->model_extension_module_telegram_core->getMessageOutput($chat_data['output_id']);
				if ($result['displayed'] == 0 ) {
					$this->model_extension_module_telegram_core->unarchiviedChat($chat_data['chat_id']);
				}
			}
		}
		
		/** -------------------------------- */
		/** List chat new message and archived elements */
		/** -------------------------------- */
		$chats = $this->model_extension_module_telegram_core->getChats();
		foreach ($chats as $chat) {
			$n_chat = $this->model_extension_module_telegram_core->getTotalMessagesInout($chat['chat_id']);
			if ($chat['archived_chat'] == 0) {
				$data['chats'][] = array(
					'chat_id'		=> $chat['chat_id'],
					'chat_type'		=> $chat['chat_type'],
					'chat_username' => $chat['chat_username'],
					'chat_firtname' => $chat['chat_first_name'],
					'chat_lastname' => $chat['chat_last_name'],
					'selected'		=> 0,
					'n_messages'	=> $n_chat,
					'url'			=> $this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $chat['chat_id'], true),
					'archived'		=> $this->url->link('extension/module/telegram_bot_core/archived', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $chat['chat_id'], true),
				);
			}
			if ($chat['archived_chat'] == 1) {
				$data['archived_chats'][] = array(
					'chat_id'		=> $chat['chat_id'],
					'chat_type'		=> $chat['chat_type'],
					'chat_username' => $chat['chat_username'],
					'chat_firtname' => $chat['chat_first_name'],
					'chat_lastname' => $chat['chat_last_name'],
					'selected'		=> 0,
					'n_messages'	=> $n_chat,
					'url'			=> $this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $chat['chat_id'], true),
					'archived'		=> $this->url->link('extension/module/telegram_bot_core/unarchived', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $chat['chat_id'], true),
				);
			}
		}
		/** -------------------------------- */
		/** Contenent chat */
		/** -------------------------------- */
		if (isset($chat_id)) {
			$list_data =  $this->model_extension_module_telegram_core->getLogChat($chat_id);
			$data['chat_info'] = $this->model_extension_module_telegram_core->getChat($chat_id);
			foreach ($list_data as $chat_data) {

				if ($chat_data['event'] == 'message_input') {
					
					$data_voice = array();
					$data_video = array();
					$data_photo = array();
					$data_document = array();

					$this->model_extension_module_telegram_core->displayedInput($chat_data['message_id']);

					$result = $this->model_extension_module_telegram_core->getMessageInput($chat_data['message_id']);

					if (!empty($result['chat_username'])) {
						$user_name = $result['chat_username'];
					} else {
						$user_name = $result['chat_first_name'];
					}

					//Document Estractor.
					if (!empty($result['photo'])) {
						$photo_data = json_decode($result['photo'], true);
						foreach ($photo_data as $photo) {
							$file_data = $this->telegram->getFile($photo['file_id']);
							$data_photo[] = array(
								'file_size'	=> $photo['file_size'],
								'width'		=> $photo['width'],
								'height'	=> $photo['height'],
								'file'		=> $this->telegram->getFileUrl($file_data['result']['file_path'])
							);
						}
					}

					if (!empty($result['voice'])) {
						$voice_data = json_decode($result['voice'], true);
						$file_data = $this->telegram->getFile($voice_data['file_id']);
						$data_voice[] = array(
							'file_size'	=> $voice_data['file_size'],
							'duration'	=> $voice_data['duration'],
							'file'		=> $this->telegram->getFileUrl($file_data['result']['file_path']),
						);
					}

					if (!empty($result['video'])) {
						$video_data = json_decode($result['video'], true);
						$file_data = $this->telegram->getFile($video_data['file_id']);
						$data_video[] = array(
							'duration'	=> $video_data['duration'],
							'file_size'	=> $video_data['file_size'],
							'width'		=> $video_data['width'],
							'height'	=> $video_data['height'],
							'file'		=> $this->telegram->getFileUrl($file_data['result']['file_path'])
						);
					}

					if (!empty($result['document'])) {
						$document_data = json_decode($result['document'], true);
						$file_data = $this->telegram->getFile($document_data['file_id']);
						$data_document[] = array(
							'file_name'	=> $document_data['file_name'],
							'file_size'	=> $document_data['file_size'],
							'file'		=> $this->telegram->getFileUrl($file_data['result']['file_path'])
						);
					}

					$data['chats_data'][] = array(
						'log_id'		=> $chat_data['log_id'],
						'name'        	=> $user_name,
						'date'  	  	=> gmdate("Y-m-d H:i:s ", $result['date']),
						'text'		  	=> $result['text'],
						'photo'			=> $data_photo,
						'video'			=> $data_video,
						'voice'			=> $data_voice,
						'document'		=> $data_document,
						'event'			=> $chat_data['event']
					);

				} else {

					$this->model_extension_module_telegram_core->displayedOutput($chat_data['output_id']);

					$this->load->model("user/user");

					$result = $this->model_extension_module_telegram_core->getMessageOutput($chat_data['output_id']);

					if ($result['author_id'] == 0 ) {
						$users_data = array('username' => 'BOT');
					} else {
						$users_data = $this->model_user_user->getUser($result['author_id']);
					}
					$data['chats_data'][] = array(
						'log_id'		=> $chat_data['log_id'],
						'name'        	=> $users_data['username'],
						'date'  	  	=> $result['date'],
						'text'		  	=> $result['text'],
						'photo'			=> '',
						'video'			=> '',
						'voice'			=> '',
						'document'		=> '',
						'event'			=> $chat_data['event']
					);

				}
			}

			$data['refresh'] = $this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $chat_id, true);
			$data['send_message'] = $this->url->link('extension/module/telegram_bot_core/send', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $chat_id, true);
		}
		
		/** -------------------------------- */
		/** End element. */
		/** -------------------------------- */
		$data['back_list'] = $this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'], true);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/module/telegram_bot_chat', $data));
	}

	public function log(){
		$this->load->model("user/user");
		$this->load->model('extension/module/telegram_core');
		$this->load->language('extension/module/telegram_bot_chat');
		$this->telegram->setKeybot($this->config->get('telegram_bot_core_token'));
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data = array();

		if (isset($this->request->get['chat_id'])) {
			$chat_id = $this->request->get['chat_id'];
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/telegram_bot_core/log', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		/** -------------------------------- */
		/** Log moviment. */
		/** -------------------------------- */

		$total_log = $this->model_extension_module_telegram_core->getTotalLog();
		
		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$list_data =  $this->model_extension_module_telegram_core->getLogs($filter_data);

		foreach ($list_data as $chat_data) {
			if ($chat_data['event'] == 'message_input') {
				$result = $this->model_extension_module_telegram_core->getMessageInput($chat_data['message_id']);
				if (!empty($result['chat_username'])) {
					$user_name = $result['chat_username'];
				} else {
					$user_name = $result['chat_first_name'];
				}

				$content = array();
				
				if (isset($result['text'])) {
					$text = $this->custom_length($result['text'], 50);
				} else {
					$text = '';
				}
				if (!empty($result['text'])) {
					$content[] = $this->language->get('text_text');
				}
	
				if (!empty($result['audio'])) {
					$content[] = $this->language->get('text_audio');
				}
	
				if (!empty($result['video'])) {
					$content[] = $this->language->get('text_video');
				}
	
				if (!empty($result['document'])) {
					$content[] = $this->language->get('text_document');
				}
	
				if (!empty($result['photo'])) {
					$content[] = $this->language->get('text_photo');
				}
	
				if (!empty($result['voice'])) {
					$content[] = $this->language->get('text_voice');
				}

				$data['logs'][] = array(
					'log_id'		=> $chat_data['log_id'],
					'name'        	=> $user_name,
					'date'  	  	=> gmdate("Y-m-d H:i:s ", $result['date']),
					'tipology'	  	=> $result['tipology'],
					'contents'	  	=> $content,
					'text'		  	=> $text
				);
				
			} else {
				$result = $this->model_extension_module_telegram_core->getMessageOutput($chat_data['output_id']);

				if ($result['author_id'] == 0 ) {
					$users_data = array('username' => 'BOT');
				} else {
					$users_data = $this->model_user_user->getUser($result['author_id']);
				}
				if (isset($result['text'])) {
					$text = $this->custom_length($result['text'], 50);
				} else {
					$text = '';
				}
				$content = array();

				if (!empty($result['text'])) {
					$content[] = $this->language->get('text_text');
				}

				if (!empty($result['document'])) {
					$content[] = $this->language->get('text_document');
				}
	
				if (!empty($result['photo'])) {
					$content[] = $this->language->get('text_photo');
				}

				$data['logs'][] = array(
					'log_id'  		=> $chat_data['log_id'],
					'name'        	=> $users_data['username'],
					'date'  	  	=> $result['date'],
					'tipology'	  	=> 'message_replied',
					'contents'	  	=> $content,
					'text'		  	=> $text
				);
				
			}
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_log;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/telegram_bot_core/log', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total_log) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_log - $this->config->get('config_limit_admin'))) ? $total_log : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_log, ceil($total_log / $this->config->get('config_limit_admin')));

		/** -------------------------------- */
		/** End element. */
		/** -------------------------------- */
		$data['settings_link'] = $this->url->link('extension/module/telegram_bot_core', 'user_token=' . $this->session->data['user_token'], true);
		$data['back_list'] = $this->url->link('extension/module/telegram_bot_core/log', 'user_token=' . $this->session->data['user_token'], true);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/module/telegram_bot_log', $data));
	}

	public function archived(){
		$this->load->model('extension/module/telegram_core');
		if (isset($this->request->get['chat_id'])) {
			$this->model_extension_module_telegram_core->archiviedChat($this->request->get['chat_id']);
			$this->response->redirect($this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'], true));
		}
		$this->response->redirect($this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function unarchived(){
		$this->load->model('extension/module/telegram_core');
		if (isset($this->request->get['chat_id'])) {
			$this->model_extension_module_telegram_core->unarchiviedChat($this->request->get['chat_id']);
			$this->response->redirect($this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'], true));
		}
		$this->response->redirect($this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function send(){
		$this->load->model('extension/module/telegram_core');
		if( $this->request->server['REQUEST_METHOD'] == 'POST' ){
			$text = $this->request->post['text'];
			$this->telegram->setKeybot($this->config->get('telegram_bot_core_token'));
			$this->telegram->sendMessage($this->request->get['chat_id'],$text);
			$data = array(
				'message_id' => '',
				'author_id' => $this->session->data['user_id'],
				'chat_id' => $this->request->get['chat_id'],
				'text' => $text,
				'document' => '',
				'photo' => ''
			);
			$this->model_extension_module_telegram_core->addMessageOutput($data);
			$this->response->redirect($this->url->link('extension/module/telegram_bot_core/chat', 'user_token=' . $this->session->data['user_token'] . '&chat_id=' . $this->request->get['chat_id'], true));
		}
	}

	private function custom_length($x, $length){
		if(strlen($x)<=$length) {
			return $x;
		} else {
			$y=substr($x,0,$length) . '...';
			return $y;
		}
	}

	public function notification(){
		$this->load->model('extension/module/telegram_core');
		$number = $this->model_extension_module_telegram_core->countInputNewMessage();
		if ($number > 0 ) {
			return '<span class="label label-warning">'.$number.'</span>';
		} else {
			return '';
		}

	}
}