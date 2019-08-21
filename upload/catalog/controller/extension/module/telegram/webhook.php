<?php

/**
 * Telegram webhook comuinication
 * @version 1.0
 * @author David Galet
 * @link http://www.davidev.it/
 *
 */

class ControllerExtensionModuleTelegramWebhook extends Controller {

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
    private $message_thanks_message = '';
    private $message_not_found = '';
    private $message_start_hedere = '';
    private $message_start_footer = '';
    private $message_help_header = '';
    private $message_help_footer = '';
    private $chat_id = '';
    private $message_id = '';
    private $request = '';

    /**
     * --------------------------
     * Add library Telegram
     * --------------------------
     */

    public function __construct($registry) {
		parent::__construct($registry);
		$registry->set('telegram', new Telegram($registry));
    }

    /**
     * --------------------------
     * check structure input data and start process
     * Get data from request post data
     * --------------------------
     */

    public function index(){
        $this->load->model('extension/module/telegram/core');
        if ($this->initialize()) {
            $content = file_get_contents("php://input");
            $post_data = json_decode($content, true);
            $this->log($post_data);
            $this->run($post_data);
            $this->log('End Cicle');
        } else {
            $this->log('Not initialize');
        }
    }

    /**
     * --------------------------
     * Run command or message and save on database
     * --------------------------
     */
    private function run($request){
        $this->load->language('extension/module/telegram/core');
        $this->load->model('extension/module/telegram/core');
        $this->log('Start run');
        $this->telegram->setKeybot($this->bot_token);
        if ($this->structures($request) == true) { //Check integrity call
            $this->request = $request;
            $this->chat_id = $this->request['message']['chat']['id'];
            $this->message_id = $this->request['message']['message_id'];
            if ($this->model_extension_module_telegram_core->responseAction($this->chat_id) == 0) { //No action
                if (isset($this->request['message']['entities'])) { //Message its commands
                    $this->addMessageInput('message_command');
                    $this->action($this->request['message']['entities'],$this->request['message']['text']);
                } else {
                    $trigger = $this->trigger($this->request['message']['text']);
                    if (!empty($trigger)) {
                        $this->addMessageInput('message_triggered');
                        $this->addMessageOutput($trigger);
                        $this->telegram->sendMessage($this->chat_id,$trigger,$this->message_id);
                    } else {
                        $this->addMessageInput('message_awaiting_reply');
                        $this->addMessageOutput($this->message_thanks_message);
                        $this->telegram->sendMessage($this->chat_id,$this->message_thanks_message,$this->message_id);
                    }
                }
            } else {
            $this->startActions();
            }
        } else {
            $this->log('Error Check integrity');
        }
    }

    /**
     * --------------------------
     * Function for log sys core
     * --------------------------
     */
    private function log($text){
        $log = new log('error_telegram.log');
        if ($this->dev) {
            $log->write($text);
        }
    }

    /**
     * --------------------------
     * This function check integrity structure of request and print status
     * --------------------------
     */
    private function initialize(){
        if ($this->config->get('telegram_bot_core_token')) {
            $this->bot_token = $this->config->get('telegram_bot_core_token');
            $this->bot_status = $this->config->get('telegram_bot_core_status');
            $this->bot_name = $this->config->get('telegram_bot_core_name');
            $this->bot_store = $this->config->get('telegram_bot_core_store');
            $this->bot_language = $this->config->get('telegram_bot_core_language');
            $this->bot_command_value = $this->config->get('telegram_bot_core_command_value');
            $this->bot_trigger_value = $this->config->get('telegram_bot_core_trigger_value');
            $this->bot_cron_url = $this->config->get('telegram_bot_core_cron_url');
            $this->bot_url_webhook = $this->config->get('telegram_bot_core_url_webhook');
            $this->message_thanks_message = $this->config->get('telegram_bot_core_thanks_message');
            $this->message_not_found = $this->config->get('telegram_bot_core_not_found');
            $this->message_start_hedere = $this->config->get('telegram_bot_core_start_heder');
            $this->message_start_footer = $this->config->get('telegram_bot_core_start_footer');
            $this->message_help_header = $this->config->get('telegram_bot_core_help_header');
            $this->message_help_footer = $this->config->get('telegram_bot_core_help_footer');
            return true;
        } else {
            return false;
        }
    }

     /**
     * --------------------------
     * AddMessage on database
     * --------------------------
     */
    private function addMessageInput($tipology){
        $this->load->model('extension/module/telegram/core');

        $data = array(
            'message_id'    => $this->request['message']['message_id'],
            'from_id'   => $this->request['message']['from']['id'],
            'from_is_bot'   => $this->request['message']['from']['is_bot'],
            'from_first_name'   => $this->request['message']['from']['first_name'],
            'from_last_name'    => $this->request['message']['from']['last_name'],
            'from_username' => $this->request['message']['from']['username'],
            'from_language_code'    => $this->request['message']['from']['language_code'],
            'chat_id'   => $this->request['message']['chat']['id'],
            'chat_type' => $this->request['message']['chat']['type'],
            'chat_username' => $this->request['message']['chat']['username'],
            'chat_first_name'   => $this->request['message']['chat']['first_name'],
            'chat_last_name'    => $this->request['message']['chat']['last_name'],
            'text'  => $this->request['message']['text'],
            'audio' => (isset($this->request['message']['audio'])) ? json_encode($this->request['message']['audio']) : '' ,
            'document'  => (isset($this->request['message']['document'])) ? json_encode($this->request['message']['document']) : '' ,
            'photo' => (isset($this->request['message']['photo'])) ? json_encode($this->request['message']['photo']) : '' ,
            'video' => (isset($this->request['message']['video'])) ? json_encode($this->request['message']['video']) : '' ,
            'voice' => (isset($this->request['message']['voice'])) ? json_encode($this->request['message']['voice']) : '' ,
            'date'  => $this->request['message']['date'],
            'tipology'  => $tipology,
            'token' => $this->bot_token
        );

        $this->model_extension_module_telegram_core->addMessageInput($data);
    }

     /**
     * --------------------------
     * AddMessage on database
     * --------------------------
     */
    private function addMessageOutput($text,$document = '',$photo = ''){
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
     * This is trigger function: Set output if the element input correspond element trigger
     * --------------------------
     */
    private function trigger($string){
        $string = strtolower($string);
        $language_id = $this->bot_language;
        if (!empty($this->bot_trigger_value)) {
            $triggers = $this->bot_trigger_value;
        }
        if (isset($triggers)) {
            foreach ($triggers as $trigger) {

                $presence_a = 0;
                $presence_b = 0;
                
                //if is present "," A Key
                if (strstr($trigger['trigger_value_keyword_a'][$language_id]['value'], ',')) {
                    $keywords_a = $trigger['trigger_value_keyword_a'][$language_id]['value']; //tempi, quanto 
                    if (strpos($string, $keywords_a) == true) {
                        ++$presence_a;
                    }
                } else {
                    $keyword_a = $trigger['trigger_value_keyword_a'][$language_id]['value']; //tempi, quanto 
                    $keywords_a = explode(',',$keyword_a);

                    foreach ($keywords_a as $ka) {
                        if (strpos($string, $ka) == true) {
                            ++$presence_a;
                        }
                    }
                }

                //if is present "," B Key
                if (strstr($trigger['trigger_value_keyword_b'][$language_id]['value'], ',')) {
                    $keywords_b = $trigger['trigger_value_keyword_b'][$language_id]['value']; //tempi, quanto 
                    if (strpos($string, $keywords_b) == true) {
                        ++$presence_a;
                    }
                } else {
                    $keyword_b = $trigger['trigger_value_keyword_b'][$language_id]['value']; //tempi, quanto 
                    $keywords_b = explode(',',$keyword_a);

                    foreach ($keywords_b as $ka) {
                        if (strpos($string, $ka) == true) {
                            ++$presence_b;
                        }
                    }
                }
    
                if ($trigger['role'] == 0) {
                    if ($presence_a > 0 || $presence_b > 0) {
                        return $trigger['trigger_value_description'][$language_id]['value'];
                        exit();
                    }
                } else {
                    if ($presence_a > 0 && $presence_b > 0) {
                        return $trigger['trigger_value_description'][$language_id]['value'];
                        exit();
                    }
                }
            }
        } else {
            return '';
        }
        
    }

    /**
     * --------------------------
     * Action command
     * --------------------------
     */
    private function action($entities,$text){
        $this->load->language('extension/module/telegram/core');
        $commands = $this->bot_command_value;
        $check = 0;
        foreach ($entities as $entitie) {
            $action = substr($text,$entitie['offset'],$entitie['length']);
            $this->log($action);

            if ($action == '/start') {
                ++$check;
                $this->start();
            }

            if ($action == '/help') {
                ++$check;
                $this->help();
            }

            if (!empty($commands)) {
                foreach ($commands as $command) {
                    if ($action == '/'.$command['command_value_description'][$this->bot_language]['name']) {
                        if ($command['status'] == 1 ) {
                            $command_url = 'extension/module/telegram/command/'.$command['command_file'];
                            $command_ins = $command_url.'/run';
                            $data_com = array(
                                'request'           => $this->request,
                                'command_url'       => $command_url,
                                'command_insert'    => $command_ins,
                                'file'              => $command['command_file'],
                                'command_action'    => '/'.$command['command_value_description'][$this->bot_language]['name'],
                                'action'            => 'run'
                            );
                            ++$check;
                            $this->log($data_com);
                            $this->load->controller($command_ins, $data_com );
                        }
                    }
                }
            }
        }
        
        $this->log($commands);

        if ($check == 0) {
            $this->addMessageOutput($this->message_not_found);
            $this->telegram->sendMessage($this->chat_id,$this->message_not_found,$this->message_id);
        }
    }

    /**
     * --------------------------
     * Start action if is presence
     * --------------------------
     */
    private function startActions(){
        $this->load->model('extension/module/telegram/core');
        $action = $this->model_extension_module_telegram_core->getAction($this->chat_id);
        $data_com = array(
            'action_old' => $action['action_old'],
            'action_new' => $action['action_new'],
            'request'    => $this->request,
        );
        $this->model_extension_module_telegram_core->completeAction($action['action_id']);
        $this->load->controller($action['action_new'], $data_com );
    }

    private function structures($data){
        $error = false;
        if (!isset($data['message'])) {
            $error = true;
            $this->log('Error its no message');
        }

        if (!isset($data['message']['form'])) {
            $error = true;
        }

        if (!isset($data['message']['chat'])) {
            $error = true;
        }

        return $error;
    }

    /**
     * --------------------------
     * Print start command
     * --------------------------
     */
    private function start(){
        $this->addMessageOutput($this->message_start_hedere);
        $this->telegram->sendMessage($this->chat_id,$this->message_start_hedere,$this->message_id);
        sleep(1);
        $this->addMessageOutput($this->message_start_footer);
        $this->telegram->sendMessage($this->chat_id,$this->message_start_footer,$this->message_id);
    }

    /**
     * --------------------------
     * Print help command
     * --------------------------
     */
    private function help(){
        $this->addMessageOutput($this->message_help_hedere);
        $this->telegram->sendMessage($this->chat_id,$this->message_help_hedere,$this->message_id);
        sleep(1);
        $this->addMessageOutput($this->message_help_footer);
        $this->telegram->sendMessage($this->chat_id,$this->message_help_footer,$this->message_id);
    }
}
