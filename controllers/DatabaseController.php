<?php
/**
 * Управление базой платежей Robokassa (sqlite)
 *
 */

namespace Controllers;

/**
 * Класс SQLite3 для работы с database
 */
class MyDB extends \SQLite3
{
    function __construct(){
        $this->open('./database/sqlite.db');
    }
}

/**
 * Класс DatabaseController для работы с базой
 */
class DatabaseController{
	
	// Database
	public $db;

	// Платежи
	public $webhooks = [];

	/**
	 * Создает базу sqlite если ее нет
	 */
	public function __construct(){
		// Инициализация базы
		$this->db = new MyDB();
		// Создаем таблицу (если ее нет)
		$this->db->exec("CREATE TABLE IF NOT EXISTS `webhooks` (
		  `id` INTEGER PRIMARY KEY NOT NULL,
		  `gateway` VARCHAR,
		  'payload' TEXT NOT NULL,
		  `headers` TEXT,
		  `status` VARCHAR,
		  `created_at` VARCHAR
		);");
	}

	/**
	 * Получить все webhook
	 */
	public function getWebhooks($gateway = "*"){
		$res = $this->db->query("SELECT * FROM `webhooks` WHERE `gateway` = \"".$gateway."\";");
		$out = [];
		while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
		    $out[] = $row;
		}
		return $out;
	}

	/**
	 * Получить webhook по id
	 * @param int $id - id webhook
	 */
	public function getWebhook($id){
		if(!$id) throw new \Exception("Id required");
		
		$res = $this->db->query("SELECT * FROM `webhooks` WHERE `id` = $id LIMIT 1;");
		return $res->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Сохранить webhook
	 * @param arr $params - параметры платежа
	 * @param str $status - статус платежа (optional)
	 */
	public function saveWebhook($payload, $headers = null, $gateway = null, $status = 'new'){
		// параметры webhook в json
		$payloadStr = $this->db->escapeString($payload);
		// заголовки
		$headersStr = $this->db->escapeString($headers);
		//$str = $this->db->escapeString(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

		// готовим insert
		$stmt = $this->db->prepare("INSERT INTO `webhooks` (gateway, payload, headers, status, created_at) VALUES (:gateway, :payload, :headers, :status, :created_at);");
		
		$stmt->bindValue(':gateway', $gateway);
		$stmt->bindValue(':payload', $payloadStr);
		$stmt->bindValue(':headers', $headersStr);
		$stmt->bindValue(':status', $status);
		$stmt->bindValue(':created_at', (string) (new \DateTime)->format(DATE_ATOM));
		$stmt->execute();
	}

	/**
	 * Обновить статус webhook
	 */
	public function updateWebhookStatus($id, $status)
	{
		$this->db->exec("UPDATE `webhooks` SET `status` = ". $status .
			" WHERE `id` = ". $id .";");
	}

	/**
	 * Удалить webhook
	 * @param int $id - id webhook
	 */
	public function deleteWebhook($id){
		$this->db->exec("DELETE FROM `webhooks` WHERE `id` = ". $id .";");
	}
	
}
	


?>
