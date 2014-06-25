<?php

namespace Zend\Tfidf\Model;

use Zend\Tfidf\Model\DocumentModel;
//require_once 'Document.class.php';

class CorpusModel
{
	protected $query_adress;
	protected $files_list;
	protected $Doc_list;
	protected $list_size;
	protected $idf;

	public function __construct($query_adress)
	{
		$this->files_list = $this->getref();
		$this->files_list[] = $query_adress;
		$this->query_adress = $query_adress;

		foreach ($this->files_list as $file_addr) {
			$file_content = $this->extractHTML($file_addr);
			if ($file_content === FALSE)
				continue ;
			$this->Doc_list[] = new DocumentModel($file_content);
		}
		$this->list_size = count($this->Doc_list);
		$this->build_idf();
	}

	private function getref()
	{
		return array(
					 'http://en.wikipedia.org/wiki/Information_technology',
					 'http://en.wikipedia.org/wiki/Politics',
					 'http://en.wikipedia.org/wiki/Economics',
					 'http://en.wikipedia.org/wiki/Science',
					 'http://en.wikipedia.org/wiki/Health',
					 'http://en.wikipedia.org/wiki/Sport',
					 'http://en.wikipedia.org/wiki/Astronomy',
					 'http://en.wikipedia.org/wiki/Fashion',
					 'http://en.wikipedia.org/wiki/Internet',
					 );
	}

	protected function extractHTML($adress)
	{
		$html_raw = file_get_contents($adress);
		$dom = new \domDocument;
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
		return ($this->Doc_list[$this->list_size - 1]->gettfidf());
	}
}
/*
$corpus = new Corpus('ref', "http://gadgets.ndtv.com/apps/news/linkedin-unveils-new-app-for-job-seekers-545481");
print_r($corpus->gettfidf());
*/