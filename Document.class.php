<?php

class Document
{
	protected $words;
	protected $tf_matrix;
	protected $tfidf_matrix;

	public function __construct($string)
	{
		$this->tfidf_matrix = null;
		if (isset($string))
		{
			$string = strtolower($string);
			$this->words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $string, -1, PREG_SPLIT_NO_EMPTY);
			$this->build_tf();
		}
		else
		{
			$this->words = null;
			$this->tf_matrix = null;
		}
	}

	public function build_tf()
	{
		if (isset($this->tf_matrix) && $this->tf_matrix)
			return ;

		$this->tfidf_matrix = null;
		$words_count = count($this->words);
		$words_occ = array_count_values($this->words);
		foreach ($words_occ as $word => $amount)
			$this->tf_matrix[$word] = $amount / $words_count;
		arsort($this->tf_matrix);
	}

	public function build_tfidf($idf)
	{
		if (isset($this->tfidf_matrix) && $this->tfidf_matrix)
			return true;
		if (!isset($this->tf_matrix) || !$this->tf_matrix)
			return false;
		if (!isset($idf) || !$idf)
			return false;

		foreach ($this->tf_matrix as $word => $word_tf)
			$this->tfidf_matrix[$word] = $word_tf * $idf[$word];
		arsort($this->tfidf_matrix);
		return true;
	}

	public function getWords()
	{
		return ($this->words);
	}

	public function getTf()
	{
		return ($this->tf_matrix);
	}

	public function getTfidf()
	{
		return ($this->tfidf_matrix);
	}
}