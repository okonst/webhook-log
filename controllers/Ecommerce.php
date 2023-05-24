<?php

/**
 * Управление webhook платежной системы Ecommerce
 *
 */
namespace Controllers;

use Controllers\DatabaseController;

/**
 * Класс для работы с webhhook
 */
class Ecommerce
{
	// База данных
	private $db;
	
	function __construct(DatabaseController $db)
	{
		$this->db = $db;
	}

	/**
	 * Сохранить новый webhook
	 */
	public function saveWebhook($request)
	{
		// заголовки
		$headers = $request->headers();
		$headers->remove('Host');
		$headers->remove('Content-Length');
		$headers->remove('Expect');
		$headers = json_encode($headers->all(), JSON_PRETTY_PRINT);
		
		// body json
		$payload = $request->body();
		// сохраняем webhook
		$this->db->saveWebhook($payload, $headers, 'ecommerce');
	}

	/**
	 * Синхронизировать webhooks с сервером
	 */
	public function sync()
	{
		//$newWebhooks = $this->processNewWebhooks();
		// отправляем запрос на сервер
		$ch = curl_init( ECOMMERCE_WEBHOOK_SYNC_URL . "/new-ecommerce" );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, ['gateway' => 'ecommerce'] );
		//curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'signature: werfc134rc234rc234'));
		# Return response instead of printing.
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);
		# Print response.
		echo "<pre>$result</pre>";


		//return json_encode($newWebhooks, JSON_PRETTY_PRINT);
	}

	/**
	 * Отправляет webhook на локальный сервер
	 */
	public function sendWebhookLocal($webhook)
	{
		$ch = curl_init( ECOMMERCE_WEBHOOK_LOCAL_URL );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $webhook->payload);
		//////////////
		//////////////
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'signature: werfc134rc234rc234'));
		# Return response instead of printing.
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);
		# Print response.
		echo "<pre>$result</pre>";
	}

	/**
	 * Обрабатывает новые webhooks за последние 5 минут
	 */
	public function processNewWebhooks()
	{
		$webhooks = $this->db->getWebhooks('ecommerce');

		$time5min = new \DateTime("5 minutes ago");

		$newWebhooks = [];
		foreach ($webhooks as $webhook) {
			// в статусе 'new'
			if($webhook->status != 'new') continue;
			// за последние 5 минут
			if($webhook->created < $time5min) continue;
			$newWebhooks[] = [
				'payload' => json_decode($webhook->payload),
				'headers' => json_decode($webhook->headers)
			];
			// ставим статус 'processed'
			$this->db->updateWebhookStatus($webhook->id, 'processed');
		}

		return json_encode($newWebhooks);
	}

	/**
	 * Формирует список webhooks Ecommerce
	 */
	public function prepareWebhooksList()
	{
		$webhooks = $this->db->getWebhooks('ecommerce');

		$table = "<table class='table table-condensed table-bordered'><thead><tr>".
		"<th>Id</th>".
		"<th>Gateway</th>".
		"<th>Payload</th>".
		"<th>Headers</th>".
		"<th>Created</th>".
		"<th>Actions</th>".
		"</tr></thead><tbody>";

		foreach ($webhooks as $key => $webhook) {
			$btnDelete = "<a class='btn btn-danger btn-sm' href='/remove?id={$webhook['id']}' style='margin-bottom:5px;'>Delete</a>";
			$btnHandle = "<a class='btn btn-primary btn-sm' href='/handle?id={$webhook['id']}' style='margin-bottom:5px;'>Handle</a>";

			//$payload = json_encode(unserialize($webhook['payload']), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
			$payload = $webhook['payload'];
			$headers = $webhook['headers'];

			$table .= "<tr>";
			$table .= "<td>{$webhook['id']}</td>";
			$table .= "<td>{$webhook['gateway']}</td>";
			$table .= "<td class='payload'><pre>{$payload}</pre></td>";
			$table .= "<td class='headers'><pre>{$headers}</pre></td>";
			$created = (new \DateTime($webhook['created_at']))->format('d.m.Y H:i');
			$table .= "<td>{$created} <br>{$webhook['status']}</td>";
			$table .= "<td>$btnHandle $btnDelete</td>";
			$table .= "</tr>";
		}

		if(count($webhooks) == 0) $table .= "<tr><td colspan=6 align='center'>Webhooks отсутствуют</td></tr>";

		$table .= "</tbody></table>";

		return $table;
	}


}


