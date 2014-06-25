<?php

include './Document.class.php';

class Corpus
{
	protected $query_adress;
	protected $files_list;
	protected $Doc_list;
	protected $list_size;
	protected $idf;

	public function __construct($reffile, $query_adress)
	{
		$reffile = file_get_contents($reffile);
		$this->files_list = explode(PHP_EOL, $reffile);
		$this->files_list[] = $query_adress;
		$this->query_adress = $query_adress;

		foreach ($this->files_list as $file_addr) {
			$file_content = $this->extractHTML($file_addr);
			if ($file_content === FALSE)
				continue ;
			$this->Doc_list[] = new Document($file_content);
		}
		$this->list_size = count($this->Doc_list);
		$this->build_idf();
	}

	protected function extractHTML($adress)
	{
		$html_raw = file_get_contents($adress);
		$dom = new domDocument;
		@$dom->loadHTML($html_raw);
		$dom->preserveWhiteSpace = false;
		$tables = $dom->getElementsByTagName('p');
		foreach ($tables as $table)
			@$string_out = $string_out . strip_tags($dom->saveHTML($table)) . PHP_EOL;
		return ($string_out);
	}

	private function build_idf()
	{
		$array_glob = array();

		foreach ($this->Doc_list as $Doc_curr) {
			$array_glob = array_merge($array_glob, array_keys($Doc_curr->getTf()));
		}
		$words_occ = array_count_values($array_glob);
		foreach ($words_occ as $word => $amount)
			$this->idf[$word] = log($this->list_size / $amount);
	}

	private function build_tfidf()
	{
		if (!isset($this->idf))
			return ;

		foreach ($this->Doc_list as $Doc_curr)
			$Doc_curr->build_tfidf($this->idf);
	}

	public function gettfidf()
	{
		$this->build_tfidf();

		print_r($this->Doc_list[$this->list_size - 1]->gettfidf());
		/*
		foreach ($this->Doc_list as $key => $Doc_curr)
		{
			print("tfidf tab for ".$this->files_list[$key].PHP_EOL);
			print_r($Doc_curr->gettfidf());			
		}
		*/
	}
}

$corpus = new Corpus('ref', "http://gadgets.ndtv.com/apps/news/linkedin-unveils-new-app-for-job-seekers-545481");
$corpus->gettfidf();