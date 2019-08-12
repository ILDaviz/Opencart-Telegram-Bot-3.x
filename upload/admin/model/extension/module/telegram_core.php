<?php
/**
 * Telegram webhook comuinication
 * @version 172
 * @author David Galet
 * @link http://www.davidev.it/
 *
 */
class ModelExtensionModuleTelegramCore extends Model {
    public function install(){
      $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "telegram_messages_input (message_id int(20),from_id int(20),from_is_bot int(1),from_first_name text,from_last_name text,from_username text,from_language_code text,chat_id int(20),chat_type text,chat_username text,chat_first_name text,chat_last_name text,text text,audio text,document text,photo text,video text,voice text,date text,tipology text,token text,displayed int(1));");

      $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "telegram_chat (chat_id int(20),chat_type text,chat_username text,chat_first_name text,chat_last_name text,archived_chat int(1),PRIMARY KEY (chat_id));");

      $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "telegram_messages_output (output_id int(20) NOT NULL AUTO_INCREMENT,author_id int(20),message_id int(20),chat_id int(20),text text,document text,photo text,displayed int(1),date DATETIME,PRIMARY KEY (output_id));");

      $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "telegram_log (log_id int(20) NOT NULL AUTO_INCREMENT,event text,chat_id int(20),message_id int(20),output_id int(20),action_id int(20),register int(1),date DATETIME,PRIMARY KEY (log_id));");

      $this->db->query("
      CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "telegram_actions (action_id int(20) NOT NULL AUTO_INCREMENT,chat_id int(20),action_old text,action_new text,status_action int(1),bot_command text,PRIMARY KEY (action_id));");
    }

    public function uninstall() {
		  $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "telegram_messages_input`");
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "telegram_messages_output`");
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "telegram_log`");
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "telegram_actions`");
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "telegram_chat`");
    }

    /** Log */

    public function addLog($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_log SET event       = '".$this->db->escape($data['event'])."', chat_id     = '".(int)$data['chat_id']."', message_id    = '".(int)$data['message_id']."', output_id   = '".(int)$data['output_id']."', action_id   = '".(int)$data['action_id']."',  register    = '".(int)$data['register']."', date = NOW()");
    }

    public function getLogChat($chat_id){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_log WHERE chat_id = '".(int)$chat_id."' ORDER BY date ASC ");
      return $query->rows;
    }

    public function getLogs($data = array()){
      $sql = "SELECT * FROM " . DB_PREFIX . "telegram_log";
      if (isset($data['sort'])){
        $sql .= " ORDER BY " . $data['sort'];
      } else {
        $sql .= " ORDER BY date ";
      }

      if (isset($data['order']) && ($data['order'] == 'ASC')) {
        $sql .= " ASC";
      } else {
        $sql .= " DESC";
      }

      if (isset($data['start']) || isset($data['limit'])) {
        if ($data['start'] < 0) {
          $data['start'] = 0;
        }

        if ($data['limit'] < 1) {
          $data['limit'] = 20;
        }

        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
      }
      $query = $this->db->query($sql);
      return $query->rows;
    }

    public function getLog($chat_id){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_log WHERE chat_id = '".(int)$chat_id."'");
      return $query->row;
    }

    public function getTotalLog(){
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "telegram_log");
      return $query->row['total'];
    }

    /** Chats */

    public function getMessagesChat($chat_id){
      $list_messages = $this->getLogChat($chat_id);
      $chat_list = array();
      foreach ($list_messages as $message) {
        if (isset($message['input_id'])) {
          $data_message = $this->getMessageInput($message['message_id']);
          $chat_list[] = array(
            'type'      => 'Input',
            'chat_id'   => $chat_id,
            'text'      => $data_message['text'],
            'author_id' => '',
            'date'      => $data_message['date'],
            'audio'     => $data_message['audio'],
            'document'  => $data_message['document'],
            'photo'     => $data_message['photo'],
            'video'     => $data_message['video'],
            'voice'     => $data_message['voice'],
          );
        }
        if (isset($message['output_id'])) {
          $data_message = $this->getMessageOutput($message['output_id']);
          $chat_list[] = array(
            'type'      => 'Output',
            'chat_id'   => $chat_id,
            'text'      => $data_message['text'],
            'author_id' => $data_message['author_id'],
            'date'      => $data_message['date'],
            'audio'     => $data_message['audio'],
            'document'  => $data_message['document'],
            'photo'     => $data_message['photo'],
            'video'     => $data_message['video'],
            'voice'     => $data_message['voice'],
          );
        }
      }
      return $chat_list;
    }

    public function getTotalMessagesInput(){
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "telegram_messages_input");

      return $query->row['total'];
    }

    public function getTotalMessagesInout($chat_id){
      $queryIn = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "telegram_messages_input WHERE chat_id = '".(int)$chat_id."' AND displayed = 0 ");
      $queryOut = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "telegram_messages_output WHERE chat_id = '".(int)$chat_id."' AND displayed = 0 ");
      $tot = $queryIn->row['total'] + $queryOut->row['total'];
      return $tot;
    }

    public function getChats(){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_chat");
      return $query->rows;
    }

    public function archiviedChat($chat_id){
      $this->db->query("UPDATE " . DB_PREFIX . "telegram_chat SET archived_chat = 1 WHERE chat_id = '".(int)$chat_id."'");
    }

    public function unarchiviedChat($chat_id){
      $this->db->query("UPDATE " . DB_PREFIX . "telegram_chat SET archived_chat = 0 WHERE chat_id = '".(int)$chat_id."'");
    }

    public function getChat($chat_id){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_chat WHERE chat_id   = '".(int)$chat_id."'");
      return $query->row;
    }

    public function getTotalChats(){
      $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "telegram_chat ");
      return $query->row['total'];
    }

    public function displayedInput($message_id){
      $this->db->query("UPDATE " . DB_PREFIX . "telegram_messages_input SET displayed = 1 WHERE message_id = '".(int)$message_id."'");
    }

    public function countInputNewMessage(){
      $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "telegram_messages_input WHERE displayed = 0 ");
      return $query->row['total'];
    }

    public function displayedOutput($output_id){
      $this->db->query("UPDATE " . DB_PREFIX . "telegram_messages_output SET displayed = 1 WHERE output_id = '".(int)$output_id."'");
    }

    public function addMessageOutput($data){
      $this->db->query("INSERT INTO " . DB_PREFIX . "telegram_messages_output SET  message_id    = '".(int)$data['message_id']."',author_id     = '".(int)$data['author_id']."',chat_id       = '".(int)$data['chat_id']."',text          = '".$this->db->escape($data['text'])."',document      = '".$this->db->escape($data['document'])."',photo         = '".$this->db->escape($data['photo'])."',displayed     = 1,date = NOW()");

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
    
    public function getMessageInput($message_id){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_messages_input WHERE message_id = '".(int)$message_id."'");
      return $query->row;
    }


    public function getMessageOutput($output_id){
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "telegram_messages_output WHERE output_id = '".(int)$output_id."'");
      return $query->row;
    }
}