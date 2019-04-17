<?php

namespace app\models\telegram;

class TelegramBotAPI
{
	private $curl, $token, $assoc;

	public function __construct(string $token, array $options = [])
	{
		$this->token = $token;
		$this->assoc = empty($options['assoc']) ? false : $options['assoc'];
	}

	private function exec($assoc = false)
	{
		$response = curl_exec($this->curl);

		curl_close($this->curl);

		if ($response === false) {
			throw new \Exception("Request Failed", 1);
		}

		$response = json_decode($response, $this->assoc);

		if ($assoc) {
			if ($response['ok'] === false) {
				throw new \Exception($response['description'], 1);
			}

			return $response['result'];
		}

		if ($response->ok === false) {
			throw new \Exception($response->description, 1);
		}

		return $response->result;
	}

	public function getUpdates($offset = 0/*, $limit = 100, $timeout = 0, $allowed_updates = ['message'] */)
	{
		$this->curl = \curl_init();

		curl_setopt($this->curl, CURLOPT_URL, "https://api.telegram.org/bot$this->token/getUpdates");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, [
			'offset' => $offset
		]);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		return $this->exec(true);
	}

	public function deleteWebhook()
	{
		$this->curl = \curl_init();

		curl_setopt($this->curl, CURLOPT_URL, "https://api.telegram.org/bot$this->token/deleteWebhook");
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		return $this->exec();
	}

	public function getMe()
	{
		$this->curl = \curl_init();

		curl_setopt($this->curl, CURLOPT_URL, "https://api.telegram.org/bot$this->token/getMe");
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		return $this->exec();
	}

	public function sendMessage($chat_id, $text)
	{
		$this->curl = \curl_init();

		curl_setopt($this->curl, CURLOPT_URL, "https://api.telegram.org/bot$this->token/sendMessage");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, [
			'chat_id' => $chat_id,
			'text' => $text
		]);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		return $this->exec();
	}

	public function getFile(string $file_id)
	{
		$this->curl = \curl_init();

		curl_setopt($this->curl, CURLOPT_URL, "https://api.telegram.org/bot$this->token/getFile");
		curl_setopt($this->curl, CURLOPT_POST, true);

		curl_setopt($this->curl, CURLOPT_POSTFIELDS, [
			'file_id' => $file_id
		]);

		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		$response = $this->exec();

		$this->curl = \curl_init();

		curl_setopt($this->curl, CURLOPT_URL, "https://api.telegram.org/file/bot$this->token/$response->file_path");
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($this->curl);

		curl_close($this->curl);

		return $response;
	}
}
