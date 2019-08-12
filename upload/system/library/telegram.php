<?php
/**
 * Telegram webhook comuinication
 * @version 1.0
 * @author David Galet
 * @link http://www.davidev.it/
 *
 */
/**
* Log class
*/
class Telegram {

    private $key_bot = '';

    public function setKeybot($key_bot){
        $this->key_bot = $key_bot;
    }

    public function getMe($key_bot){
        $this->setKeybot($key_bot);
        $info = $this->Call('getMe','POST');
        return $info;
    }

    public function getUpdates($offset = 0,$limit = 100,$timeout = 0,$allowed_updates = ''){
        $get_data = '';
        $get_data .= 'offset='.$offset.'&';
        $get_data .= 'limit='.$limit.'&';
        $get_data .= 'timeout='.$timeout.'&';
        $get_data .= 'allowed_updates='.$allowed_updates;
        $info = $this->Call('getUpdates','POST',$get_data);
        return $info;
    }

    public function setWebhook($url,$certificate = '',$max_connections = 40,$allowed_updates = ''){
        $get_data = '';
        $get_data .= 'url='.$url.'&';
        $get_data .= 'certificate='.$certificate.'&';
        $get_data .= 'max_connections='.$max_connections.'&';
        $get_data .= 'allowed_updates='.$allowed_updates;
        $info = $this->Call('setWebhook','POST',$get_data);
        return $info;
    }

    public function deleteWebhook(){
        $info = $this->Call('deleteWebhook','POST');
        return $info;
    }

    public function getWebhookInfo(){
        $info = $this->Call('getWebhookInfo','POST');
        return $info;
    }

    public function getFile($file_id){
        $get_data = 'file_id='.$file_id;
        $info = $this->Call('getFile','POST',$get_data);
        return $info;
    }

    public function getFileUrl($file_path){
        $url = 'https://api.telegram.org/file/bot' . $this->key_bot . '/' . $file_path;
        return $url;
    }

    public function sendMessage($chat_id,$text,$reply_to_message_id = '',$parse_mode = 'HTML',$disable_web_page_preview = FALSE,$disable_notification = FALSE){
        $get_data = '';
        $get_data .='chat_id='.$chat_id.'&'; 
        $get_data .='text='.$text.'&';
        $get_data .='parse_mode='.$parse_mode.'&';
        $get_data .='disable_web_page_preview='.$disable_web_page_preview.'&';
        $get_data .='disable_notification='.$disable_notification.'&';
        $get_data .='reply_to_message_id='.$reply_to_message_id;
        $info = $this->Call('sendMessage','GET',$get_data);
        return $info;
    }
    
    private function Call($command,$methods,$get_data = NULL){
        $get_data_print = '';
        if (isset($get_data)) { $get_data_print .= '?'.$get_data; }
        $url = 'https://api.telegram.org/bot'.$this->key_bot.'/'.$command.$get_data_print;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methods);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, TRUE);
	}
}