<?php
$row = 1;
if (($handle = fopen("tags.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $tag = $data[0];
        $hasChildren = $data[1] == "true";
        $comment = utf8_encode(htmlentities($data[2], ENT_NOQUOTES, 'ISO-8859-15'));
        
        $majTag = advanceducfirst($tag);

        //echo $tag."\n"; continue;
        
        $file = "<?php
namespace Mouf\Html\Tags;

use Mouf\Html\Tags\GlobalAttributesTrait;
use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Utils\Value\ValueInterface;
";
    	if ($hasChildren) {
    		$file .= "use Mouf\Html\Tags\ChildrenTrait;
";
    	}

    	$file .= "
/**
 * A &lt;$tag&gt; tag in HTML.
".turnIntoComment($comment)."
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class $majTag implements HtmlElementInterface {
";
    	if ($hasChildren) {
    		$file .= "	use ChildrenTrait;
";
    	}

    	$file .= "	use GlobalAttributesTrait;
";

    	$i=3;
    	
    	while (isset($data[$i]) && !empty($data[$i])) {
    	
    		$file .= "
    /**
".turnIntoComment($data[$i+1],"\t")."
	 * 
	 * @return string|ValueInterface
	 */
	public function get".advanceducfirst($data[$i])."() {
		return \$this->attributes['".$data[$i]."'];
	}
	
	/**
".turnIntoComment($data[$i+1],"\t")."
	 * 
	 * @param string|ValueInterface \$".varname($data[$i])."
	 * @return static
	 */
	public function set".advanceducfirst($data[$i])."(\$".varname($data[$i]).") {
		\$this->attributes['".$data[$i]."'] = \$".varname($data[$i]).";
		return \$this;
	}";
    		$i+=2;
    	}
    	
	$file .= "
	/**
	 * Renders the object in HTML.
	 * The Html is echoed directly into the output.
	 */
	public function toHtml() {
		echo '<$tag '.\$this->getAttributes().'>'.\$this->renderChildren().'</$tag>';
	}
}";
        file_put_contents($majTag.'.php', $file);
        
    }
    fclose($handle);
}


function turnIntoComment($comment, $prepend = "") {
	$comments = explode("\n", $comment);
	$comments = array_map(function($line) use ($prepend) {
		return $prepend.' * '.$line;
	}, $comments);
	
	return implode("\n", $comments);
}

function advanceducfirst($name) {
	return str_replace('-', '', ucfirst($name));
}
function varname($name) {
	return str_replace('-', '', $name);
}
?>