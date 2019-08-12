<?php
/**
 * @author    David Galet
 * @link    https://www.davidev.it
*/
class ModelExtensionModuleTelegramCore extends Model {

    public function addMessageInput($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_messages_input SET  
      message_id            = '".(int)$data['message_id']."',
      from_id               = '".(int)$data['from_id']."',
      from_is_bot           = '".(int)$data['from_is_bot']."',
      from_first_name       = '".$this->db->escape($data['from_first_name'])."',
      from_last_name        = '".$this->db->escape($data['from_last_name'])."',
      from_username         = '".$this->db->escape($data['from_username'])."',
      from_language_code    = '".$this->db->escape($data['from_language_code'])."',
      chat_id               = '".(int)$data['chat_id']."',
      chat_type             = '".$this->db->escape($data['chat_type'])."',
      chat_username         = '".$this->db->escape($data['chat_username'])."',
      chat_first_name       = '".$this->db->escape($data['chat_first_name'])."',
      chat_last_name        = '".$this->db->escape($data['chat_last_name'])."',
      text                  = '".$this->db->escape($data['text'])."',
      audio                 = '".$this->db->escape($data['audio'])."',
      document              = '".$this->db->escape($data['document'])."',
      photo                 = '".$this->db->escape($data['photo'])."',
      video                 = '".$this->db->escape($data['video'])."',
      voice                 = '".$this->db->escape($data['voice'])."',
      date                  = '".$this->db->escape($data['date'])."',
      tipology              = '".$this->db->escape($data['tipology'])."',
      displayed             = 0,
      token                 = '".$this->db->escape($data['token'])."'");

      if ($this->getPresenceChat((int)$data['chat_id']) == 0) {
        $this->addChat($data);
      }

      $data_log = array(
        'event'     => 'message_input',
        'chat_id'   => $data['chat_id'],
        'message_id'  => $data['message_id'],
        'output_id' => '',
        'action_id' => '',
        'register'  => 0,
      );
      $this->addLog($data_log);
    }

    public function addChat($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_chat SET  
      chat_id           = '".(int)$data['chat_id']."',
      chat_type         = '".$this->db->escape($data['chat_type'])."',
      chat_username     = '".$this->db->escape($data['chat_username'])."',
      chat_first_name   = '".$this->db->escape($data['chat_first_name'])."',
      chat_last_name    = '".$this->db->escape($data['chat_last_name'])."',
      archived_chat = 0
      ");
    }

    public function getPresenceChat($chat_id){
      $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "telegram_chat WHERE chat_id   = '".(int)$chat_id."'");
      return $query->row['total'];
    }

    public function addMessageOutput($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_messages_output SET  
      message_id    = '".(int)$data['message_id']."',
      author_id     = '".(int)$data['author_id']."',
      chat_id       = '".(int)$data['chat_id']."',
      text          = '".$this->db->escape($data['text'])."',
      document      = '".$this->db->escape($data['document'])."',
      photo         = '".$this->db->escape($data['photo'])."',
      displayed     = 0,
      date = NOW()");

      $last_id = $this->db->getLastId();
      $data_log = array(
      'event'       => 'message_output',
      'chat_id'     => $data['chat_id'],
      'message_id'    => '',
      'output_id'   => $last_id,
      'action_id'   => '',
      'register'    => 0,
      );
      $this->addLog($data_log);
    }

    public function addLog($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_log SET
      event       = '".$this->db->escape($data['event'])."',
      chat_id     = '".(int)$data['chat_id']."',
      message_id    = '".(int)$data['message_id']."',
      output_id   = '".(int)$data['output_id']."',
      action_id   = '".(int)$data['action_id']."', 
      register    = '".(int)$data['register']."',
      date = NOW()");
    }

    public function getLastElement($token){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_messages_input WHERE token = '" . $this->db->escape($token) . "' ORDER BY message_id DESC limit 1");
      return $query->row;
    }

    public function getActions(){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_actions WHERE status_action = 0 ");
      return $query->rows;
    }

    public function responseAction($chat_id){
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "telegram_actions WHERE chat_id = '" . (int)$chat_id . "' AND status_action = '0'");
      return $query->row['total'];
    }

    public function addAction($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_actions SET
      chat_id         = '".(int)$data['chat_id']."',
      action_old      = '".$this->db->escape($data['action_old'])."',
      action_new      = '".$this->db->escape($data['action_new'])."',
      status_action   = '0',
      bot_command     = '".$this->db->escape($data['bot_command'])."'");
    }

    public function getAction($chat_id){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_actions WHERE chat_id = '".(int)$chat_id."' AND status_action = 0 ");
      return $query->row;
    }

    public function completeAction($action_id){
      $this->db->query("UPDATE " . DB_PREFIX . "telegram_actions SET status_action = 1 WHERE action_id = '".(int)$action_id."'");
    }    
}