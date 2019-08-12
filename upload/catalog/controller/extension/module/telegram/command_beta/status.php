<?php
/**
 * Telegram webhook comuinication
 * @version 1.0
 * @author David Galet
 * @link http://www.davidev.it/
 *
 */

class ControllerExtensionModuleTelegramCommandStatus extends Controller {

    private $dev = true;
    private $bot_status = '';
    private $bot_token = '';
    private $bot_name = '';
    private $bot_connection = '';
    private $bot_store = '';
    private $bot_language = '';
    private $bot_command_value = array();
    private $bot_trigger_value = array();
    private $bot_cron_url = '';
    private $bot_url_webhook = '';
    private $request = '';

    public function __construct($registry) {
		parent::__construct($registry);
		$registry->set('telegram', new Telegram($registry));
        $this->base_dir = str_replace('/system/', '/',DIR_SYSTEM);
        $this->bot_token = $this->config->get('telegram_bot_core_token');
        $this->bot_status = $this->config->get('telegram_bot_core_status');
        $this->bot_name = $this->config->get('telegram_bot_core_name');
        $this->bot_connection = $this->config->get('telegram_bot_core_connection');
        $this->bot_store = $this->config->get('telegram_bot_core_store');
        $this->bot_language = $this->config->get('telegram_bot_core_language');
        $this->bot_command_value = $this->config->get('telegram_bot_core_command_value');
        $this->bot_trigger_value = $this->config->get('telegram_bot_core_trigger_value');
        $this->bot_cron_url = $this->config->get('telegram_bot_core_cron_url');
        $this->bot_url_webhook = $this->config->get('telegram_bot_core_url_webhook');
        $this->telegram->setKeybot($this->bot_token);
    }
    /**
     * --------------------------
     * Start command add action and first basic request
     * --------------------------
     */
    public function run($data){
        $this->load->language('extension/module/telegram/command/status');
        $this->load->model('extension/module/telegram/core');
        $this->request = $data['request'];
        $data_input = array(
            'chat_id'     => $this->request['message']['chat']['id'],
            'action_old'  => $data['command_url'].'/run',
            'action_new'  => $data['command_url'].'/step_1',
            'bot_command' => $data['file']
        );
        $this->model_extension_module_telegram_core->addAction($data_input);
        $this->addMessageOutput($this->language->get('get_order_id'));
        $this->telegram->sendMessage($this->request['message']['chat']['id'],$this->language->get('get_order_id'));
    }
    /**
     * --------------------------
     * Step 1 request code order.
     * --------------------------
     */
    public function step_1($data){
        $this->load->language('extension/module/telegram/command/status');
        $this->load->model('extension/module/telegram/core');
        $this->load->model('extension/module/telegram/command/status');
        $this->request = $data['request'];

        if (is_numeric($this->request['message']['text']) == true) {
            $order_data = $this->model_extension_module_telegram_command_status->getStatus((int)$this->request['message']['text']);
            
        } elseif ($this->request['message']['text'] == 'Exit') {
            $this->addMessageOutput($this->language->get('exit_command'));
            $this->telegram->sendMessage($this->request['message']['chat']['id'],$this->language->get('exit_command'));
        } else {
            $data_input = array(
                'chat_id'     => $this->request['message']['chat']['id'],
                'action_old'  => $data['action_old'],
                'action_new'  => $data['action_new'],
                'bot_command' => $data['file']
            );
            $this->model_extension_module_telegram_core->addAction($data_input);
            $this->addMessageOutput($this->language->get('step_1_error'));
            $this->telegram->sendMessage($this->request['message']['chat']['id'],$this->language->get('step_1_error'));
        }
        $this->addMessageOutput('ciaoneeeeee');
        $this->telegram->sendMessage($data['chat_id'],'ciaoneeeeee');
    }

    /**
     * --------------------------
     * Add message output on database
     * --------------------------
     */
    protected function addMessageOutput($text,$document = '',$photo = ''){
        $this->load->model('extension/module/telegram/core');
        $data = array(
            'message_id' => $this->request['message']['message_id'],
            'author_id' => 0,
            'chat_id' => $this->request['message']['chat']['id'],
            'text' => $text,
            'document' => $document,
            'photo' => $photo
        );
        $this->model_extension_module_telegram_core->addMessageOutput($data);
    }

    /**
     * --------------------------
     * Log system if is enabled 
     * --------------------------
     */
    protected function log($text){
        $log = new log('error_telegram.log');
        if ($this->dev) {
            $log->write($text);
        }
    }

}