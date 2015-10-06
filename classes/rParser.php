<?php

class rParser {

	private $rss_url;
	private $json_file;

	function __construct($url='https://www.drupal.org/rss.xml', $file = 'data/testfile.json') {
		$this->rss_url = $url;
		$this->json_file = $file;
	}

	private function get_url_contents($url){
		$curl_handler = curl_init();
		$timeout = 5;
		curl_setopt ($curl_handler, CURLOPT_URL, $url);
		curl_setopt ($curl_handler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl_handler, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($curl_handler);
		curl_close($curl_handler);
		return $data;
	}

	private function convert_xml() {
		$x = simplexml_load_string($this->get_url_contents($this->rss_url), "SimpleXMLElement", LIBXML_NOCDATA);
		$j = json_encode($x);
		return json_decode($j, true);
	}

	private function extract_latest_article() {
		/*
		These tags are specific to Dupal's rss scheme, 
		as Romatsa.ro's portal is running some earlier 
		version of it
		*/
		$xml_array = $this->convert_xml();
		$processed_description = strip_tags($xml_array['channel']['item'][0]['description']);
		$processed_description = str_replace("citiţi mai departe", "", $processed_description);
		$processed_description = trim($processed_description);
		$article = array(
			"title" => $xml_array['channel']['item'][0]['title'],
			"description" => $processed_description,
			"link" => $xml_array['channel']['item'][0]['link'],
			"date" => $xml_array['channel']['item'][0]['pubDate'],
		);
		return $article;
	}

	private function read_json_file($f) {
		if (!file_exists($f)) {
			$empty_article = array("title" => "", "description" => "", "link" => "", "date" => "", );
			file_put_contents($f, json_encode($empty_article));
		}
		$file = file_get_contents($f);
		$data = json_decode($file, true);
		return $data;
	}

	private function write_json_file($f, $data) {
		file_put_contents($f, json_encode($data));
	}

	private function article_freshness($old, $new) {
		if ($new["title"] != $old["title"]) {
			return true;
		} else {
			return false;
		}
	}

	private function send_email($to, $data) {
		$header = "From: Romatsa Article Parser <rparser@glogovetan.com>\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: text/html; charset=UTF-8\r\n";
		$body = file_get_contents("email/template.inc");
		$body = strtr ( $body , array('{TITLE}' => $data['title'], '{DESCRIPTION}' => $data['description'], '{LINK}' => $data['link'], '{DATE}' => $data['date'] ));
		mail($to, "Este un articol nou pe Romatsa.ro", $body, $header);
	}

	function check() {
		global $e;
		$old_article_data = $this->read_json_file($this->json_file);
		$new_article_data = $this->extract_latest_article();
		if ($this->article_freshness($old_article_data, $new_article_data)) {
			// Write the new article data into the file
			$this->write_json_file($this->json_file, $new_article_data);
			// Alert people
			for ($i=0; $i<count($e); $i++) {
				$this->send_email($e[$i]['name']." <".$e[$i]['email'].">", $new_article_data);
			}
		}
	}
}