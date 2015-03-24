<?php


class Markdown extends Michelf\Markdown {

	public $no_markup = true;
	public $no_entities = true;
	public $languages =  array('bash', 'cs', 'ruby', 'diff', 'javascript',
		'css', 'xml', 'http', 'java', 'php', 'python', 'sql', 'ini', 'perl',
		'json', 'cpp', 'markdown', 'no-highlight');

	# Adds strike with ~~ (basic)
	protected function doItalicsAndBold($text) {
		$text = parent::doItalicsAndBold($text);
		$text = preg_replace('/~~([^~]+)~~/s', '<del>$1</del>', $text);
		return $text;
	}

	# Adds code blocks with ```
	protected function doCodeBlocks($text) {
		$text = parent::doCodeBlocks($text);
		return $this->doFencedCodeBlocks($text);
	}

	protected function doFencedCodeBlocks($text) {
		$text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
				(?:~{3,}|`{3,}) # 3 or more tildes/backticks.
				)
				[ ]*
				(?:
				\.?([-_:a-zA-Z0-9]+) # 2: standalone class name
				)?
				[ ]* \n # Whitespace and newline following marker.
				# 4: Content
				(
				(?>
				(?!\1 [ ]* \n) # Not a closing marker.
				.*\n+
				)+
				)
				# Closing marker.
				\1 [ ]* (?= \n )
			}xm',
			array($this, '_doFencedCodeBlocks_callback'), $text);
		return $text;
	}

	protected function _doFencedCodeBlocks_callback($matches) {
		$classname =& $matches[2];
		$codeblock = $matches[3];
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		$codeblock = preg_replace_callback('/^\n+/',
		array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
		$attr_str = '';
		if ($classname != "" && in_array($classname, $this->languages)) {
			$attr_str = ' class="'.$classname.'"';
		}
		$codeblock = "<pre><code$attr_str>$codeblock</code></pre>";
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}

	protected function _doFencedCodeBlocks_newlines($matches) {
		return str_repeat("<br$this->empty_element_suffix",
			strlen($matches[0]));
	}

}